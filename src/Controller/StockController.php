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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TCPDF;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Process\Process;
use App\Service\PythonPredictor;
use App\Service\RapportExportService;
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
    $telephone = '+21671000000';
    $email = 'ContacteInfo@Agriplaner.tn';

    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter un logo à gauche
    $logoLeftPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/logo.jpg';
    if (file_exists($logoLeftPath)) {
        $drawingLeft = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingLeft->setName('Logo Gauche');
        $drawingLeft->setDescription('Logo Gauche');
        $drawingLeft->setPath($logoLeftPath);
        $drawingLeft->setHeight(50);
        $drawingLeft->setCoordinates('A1');
        $drawingLeft->setWorksheet($sheet);
    }

    // Ajouter un logo à droite
    $logoRightPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/esprit.jpg'; // Chemin du deuxième logo
    if (file_exists($logoRightPath)) {
        $drawingRight = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawingRight->setName('Logo Droit');
        $drawingRight->setDescription('Logo Droit');
        $drawingRight->setPath($logoRightPath);
        $drawingRight->setHeight(50);
        $drawingRight->setCoordinates('F1'); // Position à droite
        $drawingRight->setWorksheet($sheet);
    }

    // Ajouter les informations de l'entreprise
    $sheet->setCellValue('B2', $nomEntreprise);
    $sheet->setCellValue('B3', 'Téléphone : ' . $telephone);
    $sheet->setCellValue('B4', 'Email : ' . $email);

    // Ajouter un en-tête
    $sheet->mergeCells('B6:F6');
    $sheet->setCellValue('B6', 'Rapport des Stocks');
    $sheet->getStyle('B6')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('B6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Ajouter la date de génération
    $sheet->mergeCells('B7:F7');
    $sheet->setCellValue('B7', 'Généré le : ' . date('d/m/Y à H:i'));
    $sheet->getStyle('B7')->getFont()->setItalic(true)->setSize(10);
    $sheet->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Ajouter les en-têtes de colonnes
    $headers = ['Produit', 'Quantité', 'Date Entrée', 'Date Sortie', 'Entrepôt', 'Catégorie'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '9', $header);
        $sheet->getStyle($column . '9')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($column . '9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle($column . '9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $column++;
    }

    // Ajouter les données
    $row = 10;
    foreach ($stocks as $stock) {
        $entrepots = $stock->getEntrepots();
        $entrepotsList = [];
        foreach ($entrepots as $entrepot) {
            $entrepotsList[] = $entrepot->getNom() ?? 'Entrepôt #' . $entrepot->getId();
        }
        $entrepotsString = empty($entrepotsList) ? 'N/A' : implode(', ', $entrepotsList);

        $sheet->setCellValue('A' . $row, $stock->getProduit()->getNom());
        $sheet->setCellValue('B' . $row, $stock->getProduit()->getQuantite());
        $sheet->setCellValue('C' . $row, $stock->getDateEntree()->format('d/m/Y'));
        $sheet->setCellValue('D' . $row, $stock->getDateSortie() ? $stock->getDateSortie()->format('d/m/Y') : 'N/A');
        $sheet->setCellValue('E' . $row, $entrepotsString);
        $sheet->setCellValue('F' . $row, $stock->getProduit()->getCategorie());

        $row++;
    }

    // Ajuster la largeur des colonnes
    foreach (range('A', 'F') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

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
    $telephone = '+21671000000';
    $email = 'ContacteInfo@Agriplaner.tn';

    // Initialiser TCPDF
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Définir les informations du document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Votre Application');
    $pdf->SetTitle('Export des Stocks');
    $pdf->SetSubject('Export des Stocks');
    $pdf->SetKeywords('Stock, PDF, Export');

    // Ajouter une page
    $pdf->AddPage();

    // Ajouter un logo à gauche
    $logoLeftPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/logo.jpg';
    if (file_exists($logoLeftPath)) {
        $pdf->Image($logoLeftPath, 10, 10, 30, 0, 'JPG'); // Position (x=10, y=10), largeur=30, hauteur automatique
    }

    // Ajouter un logo à droite
    $logoRightPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/esprit.jpg'; // Chemin du deuxième logo
    if (file_exists($logoRightPath)) {
        $pdf->Image($logoRightPath, 170, 10, 30, 0, 'JPG'); // Position (x=170, y=10), largeur=30, hauteur automatique
    }

    // Ajouter les informations de l'entreprise
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY(50, 15); // Position (x=50, y=15)
    $pdf->Cell(0, 10, $nomEntreprise, 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(50, 20);
    $pdf->Cell(0, 10, 'Téléphone : ' . $telephone, 0, 1);
    $pdf->SetXY(50, 25);
    $pdf->Cell(0, 10, 'Email : ' . $email, 0, 1);

    // Ajouter un en-tête
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Rapport des Stocks', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Généré le : ' . date('d/m/Y à H:i'), 0, 1, 'C');
    $pdf->Ln(10);

    // Définir les largeurs des colonnes
    $columnWidths = [
        'produit' => 40,
        'quantite' => 20,
        'date_entree' => 30,
        'date_sortie' => 30,
        'entrepots' => 50,
        'seuil_alert' => 30,
    ];

    // Ajouter l'en-tête du tableau avec des couleurs
    $pdf->SetFillColor(200, 220, 255); // Couleur de fond des en-têtes
    $pdf->SetTextColor(0, 0, 0); // Couleur du texte
    $pdf->SetFont('helvetica', 'B', 12);

    $pdf->Cell($columnWidths['produit'], 10, 'Produit', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['quantite'], 10, 'Quantité', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['date_entree'], 10, 'Date Entrée', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['date_sortie'], 10, 'Date Sortie', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['entrepots'], 10, 'Entrepôts', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['seuil_alert'], 10, 'Seuil Alerte', 1, 1, 'C', true); // Saut de ligne après cette cellule

    // Ajouter les données des stocks
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255); // Couleur de fond des cellules de données
    $pdf->SetTextColor(0, 0, 0);

    foreach ($stocks as $stock) {
        // Gérer les entrepôts associés au stock
        $entrepots = $stock->getEntrepots();
        $entrepotsList = [];
        foreach ($entrepots as $entrepot) {
            $entrepotsList[] = $entrepot->getNom();
        }
        $entrepotsString = empty($entrepotsList) ? 'N/A' : implode(', ', $entrepotsList);

        // Ajouter les cellules au PDF
        $pdf->Cell($columnWidths['produit'], 10, $stock->getProduit()->getNom(), 1, 0, 'L');
        $pdf->Cell($columnWidths['quantite'], 10, $stock->getProduit()->getQuantite(), 1, 0, 'C');
        $pdf->Cell($columnWidths['date_entree'], 10, $stock->getDateEntree()->format('d/m/Y'), 1, 0, 'C');
        $pdf->Cell($columnWidths['date_sortie'], 10, $stock->getDateSortie() ? $stock->getDateSortie()->format('d/m/Y') : 'N/A', 1, 0, 'C');
        $pdf->Cell($columnWidths['entrepots'], 10, $entrepotsString, 1, 0, 'C');
        $pdf->Cell($columnWidths['seuil_alert'], 10, $stock->getSeuilAlert() ?? 'N/A', 1, 1, 'C'); // Saut de ligne après cette cellule
    }

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
  
    private PythonPredictor $pythonPredictor;

    public function __construct(PythonPredictor $pythonPredictor)
    {
        $this->pythonPredictor = $pythonPredictor;
    }

    #[Route('/predict-demand', name: 'predict_demand', methods: ['GET'])]
    public function predictDemand(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $stockData = [
            ['product_id' => '550e8400-e29b-41d4-a716-446655440000', 'quantity' => 10, 'date' => '2023-10-01'],
            ['product_id' => '550e8400-e29b-41d4-a716-446655440000', 'quantity' => 15, 'date' => '2023-10-02'],
            // Ajoutez plus de données ici
        ];

        // Appeler le service PythonPredictor
        $predictions = $this->pythonPredictor->predict($stockData);

        return $this->json($predictions);
    }

  }