<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Categorie;
use App\Entity\Entrepot;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Form\StockType;
use App\Repository\EntrepotRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TCPDF;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
#[Route('/stock')]
class StockController extends AbstractController{
#[Route('/', name: 'app_stock_index', methods: ['GET'])]
public function index(
    StockRepository $stockRepository,
    PaginatorInterface $paginator,
    Request $request,
    EntityManagerInterface $entityManager // Injection de EntityManager
): Response {
    // Récupérer les stocks paginés
    $query = $stockRepository->createQueryBuilder('s')->getQuery();
    $stocks = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

    // Récupérer les catégories, entrepôts et fournisseurs pour les filtres
    $categories = $entityManager->getRepository(Categorie::class)->findAll();
    $entrepots = $entityManager->getRepository(Entrepot::class)->findAll();
    $fournisseurs = $entityManager->getRepository(Fournisseur::class)->findAll();

    return $this->render('stock/index.html.twig', [
        'stocks' => $stocks,
        'categories' => $categories,
        'entrepots' => $entrepots,
        'fournisseurs' => $fournisseurs,
    ]);
}
// src/Controller/StockController.php
#[Route('/stock/new', name: 'app_stock_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $stock = new Stock();

    // Récupérer les IDs des produits déjà en stock
    $existingProductIds = $entityManager->getRepository(Stock::class)
        ->createQueryBuilder('s')
        ->select('IDENTITY(s.produit) as produit_id')
        ->getQuery()
        ->getScalarResult();

    // Convertir les UUID en chaînes de caractères (si nécessaire)
    $existingProductIds = array_map(function ($id) {
        return $id instanceof Uuid ? $id->toRfc4122() : $id;
    }, array_column($existingProductIds, 'produit_id'));

    // Récupérer les produits qui ne sont pas en stock
    $qb = $entityManager->getRepository(Produit::class)
        ->createQueryBuilder('p');

    if (!empty($existingProductIds)) {
        $qb->where('p.id NOT IN (:existingProductIds)')
           ->setParameter('existingProductIds', $existingProductIds);
    }

    $produits = $qb->getQuery()->getResult();

    // Créer le formulaire avec les produits non stockés
    $form = $this->createForm(StockType::class, $stock, [
        'produits' => $produits,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifier si le produit existe déjà dans la table de stock
        $existingStock = $entityManager->getRepository(Stock::class)->findOneBy([
            'produit' => $stock->getProduit(),
        ]);

        if ($existingStock) {
            $this->addFlash('error', 'Ce produit existe déjà dans le stock.');
            return $this->redirectToRoute('app_stock_new');
        }

        $entityManager->persist($stock);
        $entityManager->flush();

        $this->addFlash('success', 'Le stock a été créé avec succès.');
        return $this->redirectToRoute('app_stock_index');
    }

    return $this->render('stock/new.html.twig', [
        'form' => $form->createView(),
        'existingProducts' => $produits, // Passer les produits non stockés au template
    ]);
}#[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
{
    // Vérifiez que le stock contient un produit
    dump($stock->getProduit()); // Cela devrait afficher le produit associé

    $form = $this->createForm(StockType::class, $stock);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Le stock a été mis à jour avec succès.');
        return $this->redirectToRoute('app_stock_index');
    }

    return $this->render('stock/edit.html.twig', [
        'form' => $form->createView(),
        'stock' => $stock,
    ]);
}
    #[Route('/{id}', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $entityManager): Response
    {
        if (!$stock) {
            $this->addFlash('error', 'Stock introuvable.');
            return $this->redirectToRoute('app_stock_index');
        }

        if ($this->isCsrfTokenValid('delete'.$stock->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($stock);
                $entityManager->flush();
                $this->addFlash('success', 'Stock supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du stock.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_stock_index');
    }

#[Route('/{id}/increase', name: 'app_stock_increase', methods: ['GET', 'POST'])]
public function increaseQuantite(Stock $stock, Request $request, EntityManagerInterface $entityManager): Response
{
    // Si le formulaire est soumis (méthode POST)
    if ($request->isMethod('POST')) {
        $amount = $request->request->getInt('amount', 0);

        if ($amount > 0) {
            // Récupérer le produit associé au stock
            $produit = $stock->getProduit();

            // Augmenter la quantité du produit
            $produit->increaseQuantite($amount);

            // Enregistrer les modifications en base de données
            $entityManager->persist($produit);
            $entityManager->flush();

            // Ajouter un message flash pour informer l'utilisateur
            $this->addFlash('success', sprintf('La quantité du produit a été augmentée de %d avec succès.', $amount));
        } else {
            $this->addFlash('error', 'La quantité doit être un nombre positif.');
        }

        // Rediriger vers la page précédente
        return $this->redirectToRoute('app_stock_index');
    }

    // Si la méthode est GET, afficher le formulaire
    return $this->render('stock/_increase_quantity_form.html.twig', [
        'stock' => $stock,
    ]);
}
#[Route("/stock/export-excel", name: "export_excel_stock")]
public function exportExcelAction(EntityManagerInterface $entityManager): Response
{
    // Désactiver la sortie tampon pour éviter toute corruption du fichier
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Récupérer les données de stock
    $stocks = $entityManager->getRepository(Stock::class)->findAll();

    // Informations statiques de l'entreprise
    $nomEntreprise = 'Agriplaner';
    $telephone = '+216 71 000 000';
    $email = 'ContacteInfo@Agriplaner.tn';

    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter un fond clair à la feuille entière
    $sheet->getStyle('A1:F' . (count($stocks) + 10))->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('F0F8FF'); // Bleu très clair (équivalent à 240, 248, 255)

    // Ajouter un logo à gauche
    $logoLeftPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/logo.jpg';
    if (file_exists($logoLeftPath)) {
        $drawingLeft = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingLeft->setName('Logo Gauche');
        $drawingLeft->setDescription('Logo Gauche');
        $drawingLeft->setPath($logoLeftPath);
        $drawingLeft->setHeight(60);
        $drawingLeft->setCoordinates('A1');
        $drawingLeft->setOffsetX(5);
        $drawingLeft->setOffsetY(5);
        $drawingLeft->setWorksheet($sheet);
    }

    // Ajouter un logo à droite
    $logoRightPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/esprit.jpg';
    if (file_exists($logoRightPath)) {
        $drawingRight = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingRight->setName('Logo Droit');
        $drawingRight->setDescription('Logo Droit');
        $drawingRight->setPath($logoRightPath);
        $drawingRight->setHeight(60);
        $drawingRight->setCoordinates('F1');
        $drawingRight->setOffsetX(-5);
        $drawingRight->setWorksheet($sheet);
    }

    // Ajouter les informations de l'entreprise
    $sheet->mergeCells('B2:E2');
    $sheet->setCellValue('B2', $nomEntreprise);
    $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB('1976D2'); // Bleu vif
    $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('B3:E3');
    $sheet->setCellValue('B3', 'Téléphone : ' . $telephone);
    $sheet->getStyle('B3')->getFont()->setSize(10)->getColor()->setRGB('424242'); // Gris foncé
    $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('B4:E4');
    $sheet->setCellValue('B4', 'Email : ' . $email);
    $sheet->getStyle('B4')->getFont()->setSize(10)->getColor()->setRGB('424242');
    $sheet->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Ligne décorative (simulée par une bordure)
    $sheet->getStyle('A5:F5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('1976D2');

    // Ajouter un en-tête
    $sheet->mergeCells('A6:F6');
    $sheet->setCellValue('A6', 'Rapport des Stocks');
    $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('00C853'); // Vert vif
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Ajouter la date de génération
    $sheet->mergeCells('A7:F7');
    $sheet->setCellValue('A7', 'Généré le : ' . date('d/m/Y à H:i'));
    $sheet->getStyle('A7')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('787878'); // Gris moyen
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Ajouter les en-têtes de colonnes
    $headers = ['Produit', 'Quantité', 'Date Entrée', 'Date Sortie', 'Entrepôts', 'Seuil Alerte'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '9', $header);
        $sheet->getStyle($column . '9')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('F5F7FA');
        $sheet->getStyle($column . '9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF'); // Bleu vif
        $sheet->getStyle($column . '9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($column . '9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('FFFFFF');
        $column++;
    }

    // Ajouter les données avec alternance de couleurs
    $row = 10;
    $fill = false;
    foreach ($stocks as $stock) {
        $entrepots = $stock->getEntrepots();
        $entrepotsList = [];
        foreach ($entrepots as $entrepot) {
            $entrepotsList[] = $entrepot->getNom() ?? 'Entrepôt #' . $entrepot->getId();
        }
        $entrepotsString = empty($entrepotsList) ? 'N/A' : implode(', ', $entrepotsList);

        // Alternance de couleurs
        $fillColor = $fill ? 'F5F7FA' : 'FFFFFF'; // Gris clair / Blanc
        $range = 'A' . $row . ':F' . $row;
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fillColor);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

        // Données
        $sheet->setCellValue('A' . $row, $stock->getProduit()->getNom());
        $sheet->setCellValue('B' . $row, $stock->getProduit()->getQuantite());
        $sheet->setCellValue('C' . $row, $stock->getDateEntree()->format('d/m/Y'));
        $sheet->setCellValue('D' . $row, $stock->getDateSortie() ? $stock->getDateSortie()->format('d/m/Y') : 'N/A');
        $sheet->setCellValue('E' . $row, $entrepotsString);

        // Mise en évidence du seuil d'alerte
        $quantite = $stock->getProduit()->getQuantite();
        $seuilAlert = $stock->getSeuilAlert();
        if ($quantite < $seuilAlert) {
            $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('FF5722'); // Orange vif
        }
        $sheet->setCellValue('F' . $row, $seuilAlert ?? 'N/A');

        $sheet->getStyle($range)->getFont()->setSize(10)->getColor()->setRGB('212121'); // Gris foncé
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        $fill = !$fill;
    }

    // Ajuster la largeur des colonnes
    foreach (range('A', 'F') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Ajouter un pied de page
    $sheet->setCellValue('A' . ($row + 1), '© 2025 Agriplaner - Généré automatiquement');
    $sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));
    $sheet->getStyle('A' . ($row + 1))->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('787878');
    $sheet->getStyle('A' . ($row + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Création du fichier Excel en mémoire
    $writer = new Xlsx($spreadsheet);
    $tempFilePath = tempnam(sys_get_temp_dir(), 'stock_') . '.xlsx';
    $writer->save($tempFilePath);

    // Vérifier que le fichier a été correctement créé
    if (!file_exists($tempFilePath) || filesize($tempFilePath) === 0) {
        throw new \Exception('Erreur lors de la génération du fichier Excel.');
    }

    // Retourner le fichier en réponse HTTP
    return new BinaryFileResponse($tempFilePath, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="stock_export_' . date('Y-m-d') . '.xlsx"',
    ], true, 'inline', true, true);
}

#[Route("/stock/export-pdf", name: "export_pdf_stock")]
public function exportPdfStockAction(EntityManagerInterface $entityManager): Response
{
    // Récupérer les données des stocks
    $stocks = $entityManager->getRepository(Stock::class)->findAll();

    // Informations statiques de l'entreprise
    $nomEntreprise = 'Agriplaner';
    $telephone = '+216 71 000 000';
    $email = 'ContacteInfo@Agriplaner.tn';

    // Initialiser TCPDF
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Définir les métadonnées du document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Agriplaner');
    $pdf->SetTitle('Rapport des Stocks - Agriplaner');
    $pdf->SetSubject('Exportation des Stocks');
    $pdf->SetKeywords('Stock, PDF, Agriplaner, Rapport');

    // Supprimer les marges par défaut pour un design personnalisé
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);

    // Ajouter une page
    $pdf->AddPage();

    // Ajouter un fond coloré subtil (dégradé)
    $pdf->SetFillColor(240, 248, 255); // Bleu très clair
    $pdf->Rect(0, 0, 210, 297, 'F'); // Fond de la page entière

    // Ajouter un filigrane (optionnel)
    $pdf->SetAlpha(0.1);
    $pdf->SetFont('helvetica', 'B', 50);
    $pdf->SetTextColor(200, 200, 200);
    $pdf->StartTransform();
    $pdf->Rotate(45, 50, 200);
    $pdf->Text(50, 200, 'Agriplaner');
    $pdf->StopTransform();
    $pdf->SetAlpha(1);

    // Ajouter les logos avec style
    $logoLeftPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/logo.jpg';
    if (file_exists($logoLeftPath)) {
        $pdf->Image($logoLeftPath, 10, 10, 40, 0, 'JPG', '', '', true, 300, '', false, false, 1, false, false, false);
    }

    $logoRightPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/esprit.jpg';
    if (file_exists($logoRightPath)) {
        $pdf->Image($logoRightPath, 160, 10, 40, 0, 'JPG', '', '', true, 300, '', false, false, 1, false, false, false);
    }

    // En-tête stylisé de l'entreprise
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(25, 118, 210); // Bleu vif
    $pdf->SetXY(50, 15);
    $pdf->Cell(110, 10, $nomEntreprise, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(66, 66, 66); // Gris foncé
    $pdf->SetXY(50, 25);
    $pdf->Cell(110, 5, 'Téléphone : ' . $telephone, 0, 1, 'C');
    $pdf->SetXY(50, 30);
    $pdf->Cell(110, 5, 'Email : ' . $email, 0, 1, 'C');

    // Ligne décorative sous l’en-tête
    $pdf->SetLineStyle(['width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => [25, 118, 210]]);
    $pdf->Line(10, 40, 200, 40);

    // Titre du rapport
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(0, 150, 136); // Vert-bleu vif
    $pdf->SetXY(0, 50);
    $pdf->Cell(0, 10, 'Rapport des Stocks', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(120, 120, 120); // Gris moyen
    $pdf->Cell(0, 5, 'Généré le : ' . date('d/m/Y à H:i'), 0, 1, 'C');
    $pdf->Ln(10);

    // Définir les largeurs des colonnes
    $columnWidths = [
        'produit' => 50,
        'quantite' => 20,
        'date_entree' => 30,
        'date_sortie' => 30,
        'entrepots' => 40,
        'seuil_alert' => 20,
    ];

    // En-tête du tableau avec dégradé
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(255,255,255); // Bleu vif
    $pdf->SetTextColor(33, 33, 33); // Texte blanc
    $pdf->Cell($columnWidths['produit'], 10, 'Produit', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['quantite'], 10, 'Quantité', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['date_entree'], 10, 'Date Entrée', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['date_sortie'], 10, 'Date Sortie', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['entrepots'], 10, 'Entrepôts', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['seuil_alert'], 10, 'Seuil Alerte', 1, 1, 'C', true);

    // Données du tableau avec alternance de couleurs
    $pdf->SetFont('helvetica', '', 10);
    $fill = false;
    foreach ($stocks as $index => $stock) {
        $entrepots = $stock->getEntrepots();
        $entrepotsList = [];
        foreach ($entrepots as $entrepot) {
            $entrepotsList[] = $entrepot->getNom();
        }
        $entrepotsString = empty($entrepotsList) ? 'N/A' : implode(', ', $entrepotsList);

        // Alternance de couleurs pour les lignes
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 247 : 255, $fill ? 250 : 255); // Gris clair / Blanc
        $pdf->SetTextColor(33, 33, 33); // Gris très foncé

        $pdf->Cell($columnWidths['produit'], 8, $stock->getProduit()->getNom(), 1, 0, 'L', true);
        $pdf->Cell($columnWidths['quantite'], 8, $stock->getProduit()->getQuantite(), 1, 0, 'C', true);
        $pdf->Cell($columnWidths['date_entree'], 8, $stock->getDateEntree()->format('d/m/Y'), 1, 0, 'C', true);
        $pdf->Cell($columnWidths['date_sortie'], 8, $stock->getDateSortie() ? $stock->getDateSortie()->format('d/m/Y') : 'N/A', 1, 0, 'C', true);
        $pdf->Cell($columnWidths['entrepots'], 8, $entrepotsString, 1, 0, 'C', true);
        
        // Mettre en évidence le seuil d'alerte si dépassé
        $quantite = $stock->getProduit()->getQuantite();
        $seuilAlert = $stock->getSeuilAlert();
        if ($quantite < $seuilAlert) {
            $pdf->SetTextColor(255, 87, 34); // Orange vif pour alerte
        }
        $pdf->Cell($columnWidths['seuil_alert'], 8, $seuilAlert ?? 'N/A', 1, 1, 'C', true);
        $pdf->SetTextColor(33, 33, 33); // Réinitialiser la couleur

        $fill = !$fill; // Alterner les couleurs
    }

    // Ajouter un pied de page stylisé
    $pdf->SetY(-20);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(0, 10, '© 2025 Agriplaner - Généré automatiquement', 0, 0, 'C');

    // Générer le nom du fichier
    $filename = 'stock_export_' . date('Y-m-d') . '.pdf';

    // Retourner le PDF en tant que réponse
    $response = new Response($pdf->Output($filename, 'S'));
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

    return $response;
}
  #[Route('/dashboard', name: 'app_stock_dashboard')]

  public function dashboardIndex(StockRepository $stockRepository, EntrepotRepository $entrepotRepository, Request $request): Response
  {
      // Récupérer la plage de temps sélectionnée (par défaut : 7 jours)
      $timeRange = $request->query->get('timeRange', '7');

      // Récupérer les données en fonction de la plage de temps
      $totalStocks = $stockRepository->getTotalStocks($timeRange);
      $activeWarehouses = $entrepotRepository->getActiveWarehousesCount();
      $alertsCount = $stockRepository->getAlertsCount($timeRange);
      $stockTrend = $stockRepository->getStockTrend($timeRange);
      $stockDistribution = $stockRepository->getStockDistribution($timeRange);
      $stockMovement = $stockRepository->getStockMovement($timeRange);
  
      return $this->render('stock/dashboard/dashboard_stock.html.twig', [
          'totalStocks' => (int) $totalStocks,
          'activeWarehouses' => (int) $activeWarehouses,
          'alertsCount' => (int) $alertsCount,
          'stockTrend' => $stockTrend,
          'stockDistribution' => $stockDistribution,
          'stockMovement' => $stockMovement,
          'timeRange' => $timeRange,
      ]);
    }
    #[Route('/predict-demand', name: 'app_stock_predict-demand')]

    public function predictDemand(): Response
    {
        // Chemin du script Python
        $pythonScriptPath = $this->getParameter('kernel.project_dir') . '/scripts/predict_demand.py';

        // Données de stock à passer au script Python
        $stockData = [
            ['product_id' => '550e8400-e29b-41d4-a716-446655440000', 'quantity' => 10, 'date' => '2023-10-01'],
            ['product_id' => '550e8400-e29b-41d4-a716-446655440000', 'quantity' => 15, 'date' => '2023-10-02'],
            ['product_id' => '123e4567-e89b-12d3-a456-426614174000', 'quantity' => 5, 'date' => '2023-10-01'],
        ];

        // Convertir les données en JSON
        $inputData = json_encode($stockData);

        // Exécuter le script Python
        $process = new Process(['python3', $pythonScriptPath, $inputData]);
        $process->run();

        // Vérifier si l'exécution a réussi
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Récupérer la sortie du script Python
        $output = $process->getOutput();
        $predictions = json_decode($output, true);

        // Passer les prédictions au template Twig
        return $this->render('stock/predictions.html.twig', [
            'predictions' => $predictions,
        ]);
    }
}

  