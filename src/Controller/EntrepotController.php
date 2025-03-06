<?php

namespace App\Controller;

use App\Entity\Entrepot;
use App\Form\EntrepotType;
use App\Repository\EntrepotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

#[Route('/entrepot')]
class EntrepotController extends AbstractController
{ 
    // Affiche la liste de tous les entrepôts
    #[Route('/', name: 'app_entrepot_index', methods: ['GET'])]
    public function index(EntrepotRepository $entrepotRepository): Response
    {
        // Récupère tous les entrepôts de la base de données
        $entrepots = $entrepotRepository->findAll();

        // Rendu de la vue en passant la liste des entrepôts
        return $this->render('entrepot/index.html.twig', [
            'entrepots' => $entrepots, // Passe la liste des entrepôts à la vue
        ]);
    }
    #[Route('/entrepot/new', name: 'app_entrepot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entrepot = new Entrepot();
        $form = $this->createForm(EntrepotType::class, $entrepot);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
          // Enregistrer l'entrepôt dans la base de données
          $entityManager->persist($entrepot);
          $entityManager->flush();
      
          // Redirection après enregistrement
          return $this->redirectToRoute('app_entrepot_index');
      } else {
          // Afficher les erreurs de validation
          foreach ($form->getErrors(true) as $error) {
              $this->addFlash('error', $error->getMessage());
          }
      }    
        return $this->render('entrepot/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_entrepot_show', methods: ['GET'])]
    public function show(Entrepot $entrepot): Response
    {
        return $this->render('entrepot/show.html.twig', [
            'entrepot' => $entrepot, // Passe l'entrepôt à la vue
        ]);
    }

    // Affiche le formulaire de modification d'un entrepôt existant
    #[Route('/{id}/edit', name: 'app_entrepot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntrepotType::class, $entrepot); // Crée le formulaire de modification

        $form->handleRequest($request); // Gère la soumission du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le formulaire est valide, met à jour l'entrepôt en base de données
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'L\'entrepôt a été modifié avec succès.');

            // Redirige vers la liste des entrepôts
            return $this->redirectToRoute('app_entrepot_index');
        }

        // Affiche le formulaire de modification
        return $this->render('entrepot/edit.html.twig', [
            'entrepot' => $entrepot,
            'form' => $form->createView(),
        ]);
    }

    // Supprime un entrepôt
    #[Route('/{id}', name: 'app_entrepot_delete', methods: ['POST'])]
    public function delete(Request $request, Entrepot $entrepot, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifie la validité du token CSRF pour éviter les attaques
        $token = new CsrfToken('delete' . $entrepot->getId(), $request->request->get('_token'));
        if ($csrfTokenManager->isTokenValid($token)) {
            // Supprime l'entrepôt de la base de données
            $entityManager->remove($entrepot);
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'L\'entrepôt a été supprimé avec succès.');
        } else {
            // Si le token CSRF est invalide, affiche une erreur
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        // Redirige vers la liste des entrepôts
        return $this->redirectToRoute('app_entrepot_index');
    }

    #[Route("/entrepot/export-pdf", name: "export_pdf_entrepot")]
    public function exportPdfEntrepotAction(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les données des entrepôts
        $entrepots = $entityManager->getRepository(Entrepot::class)->findAll();
    
        // Informations statiques de l'entreprise
        $nomEntreprise = 'Agriplaner';
        $telephone = '+216 71 000 000';
        $email = 'ContacteInfo@Agriplaner.tn';
    
        // Initialiser TCPDF
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
        // Définir les métadonnées du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Agriplaner');
        $pdf->SetTitle('Rapport des Entrepôts - Agriplaner');
        $pdf->SetSubject('Exportation des Entrepôts');
        $pdf->SetKeywords('Entrepôt, PDF, Agriplaner, Rapport');
    
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
        $pdf->Cell(0, 10, 'Rapport des Entrepôts', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->SetTextColor(120, 120, 120); // Gris moyen
        $pdf->Cell(0, 5, 'Généré le : ' . date('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Ln(10);
    
        // Définir les largeurs des colonnes
        $columnWidths = [
            'nom' => 40,
            'adresse' => 50,
            'ville' => 30,
            'espace' => 30,
            'stocks' => 50,
        ];
    
        // En-tête du tableau avec dégradé
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetFillColor(255,255,255); // Bleu vif
        $pdf->SetTextColor(33, 33, 33); // Texte blanc
        $pdf->Cell($columnWidths['nom'], 10, 'Nom', 1, 0, 'C', true);
        $pdf->Cell($columnWidths['adresse'], 10, 'Adresse', 1, 0, 'C', true);
        $pdf->Cell($columnWidths['ville'], 10, 'Ville', 1, 0, 'C', true);
        $pdf->Cell($columnWidths['espace'], 10, 'Espace (m²)', 1, 0, 'C', true);
        $pdf->Cell($columnWidths['stocks'], 10, 'Stocks', 1, 1, 'C', true);
    
        // Données du tableau avec alternance de couleurs
        $pdf->SetFont('helvetica', '', 10);
        $fill = false;
        foreach ($entrepots as $entrepot) {
            // Gérer les stocks associés à l'entrepôt
            $stocks = $entrepot->getStocks();
            $stocksList = [];
            foreach ($stocks as $stock) {
                $stocksList[] = $stock->getProduit()->getNom() ?? 'Stock #' . $stock->getId();
            }
            $stocksString = empty($stocksList) ? 'N/A' : implode(', ', $stocksList);
    
            // Alternance de couleurs pour les lignes
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 247 : 255, $fill ? 250 : 255); // Gris clair / Blanc
            $pdf->SetTextColor(33, 33, 33); // Gris très foncé
    
            $pdf->Cell($columnWidths['nom'], 8, $entrepot->getNom(), 1, 0, 'L', true);
            $pdf->Cell($columnWidths['adresse'], 8, $entrepot->getAdresse(), 1, 0, 'C', true);
            $pdf->Cell($columnWidths['ville'], 8, $entrepot->getVille() ?? 'N/A', 1, 0, 'C', true);
            $pdf->Cell($columnWidths['espace'], 8, $entrepot->getEspace(), 1, 0, 'C', true);
            $pdf->Cell($columnWidths['stocks'], 8, $stocksString, 1, 1, 'C', true);
    
            $fill = !$fill; // Alterner les couleurs
        }
    
        // Ajouter un pied de page stylisé
        $pdf->SetY(-20);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 10, '© 2025 Agriplaner - Généré automatiquement', 0, 0, 'C');
    
        // Générer le nom du fichier
        $filename = 'entrepot_export_' . date('Y-m-d') . '.pdf';
    
        // Retourner le PDF en tant que réponse
        $response = new Response($pdf->Output($filename, 'S'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    
        return $response;
    }
#[Route("/entrepot/export-excel", name: "export_excel_entrepot")]
public function exportExcelEntrepotAction(EntityManagerInterface $entityManager): Response
{
    // Désactiver la sortie tampon pour éviter toute corruption du fichier
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Récupérer les données des entrepôts
    $entrepots = $entityManager->getRepository(Entrepot::class)->findAll();
    $nomEntreprise = 'Agriplaner';
    $telephone = '+216 71 000 000';
    $email = 'ContacteInfo@Agriplaner.tn';

    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getStyle('A1:F' . (count($entrepots) + 10))->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('F0F8FF'); // Bleu très clair (équivalent à 240, 248, 255)



    // Ajouter un logo si disponible
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

    // Ajouter un en-tête
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
    $sheet->setCellValue('A6', 'Rapport des Entrepots');
    $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('00C853'); // Vert vif
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Ajouter la date de génération
    $sheet->mergeCells('A7:F7');
    $sheet->setCellValue('A7', 'Généré le : ' . date('d/m/Y à H:i'));
    $sheet->getStyle('A7')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('787878'); // Gris moyen
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


    // Ajouter les en-têtes de colonnes
    $headers = ['Nom', 'Adresse', 'Ville', 'Espace (m²)', 'Stocks'];
    $column = 'A';
    foreach ($headers as $header) {
      $sheet->setCellValue($column . '9', $header);
      $sheet->getStyle($column . '9')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('F5F7FA');
      $sheet->getStyle($column . '9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF'); // Bleu vif
      $sheet->getStyle($column . '9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle($column . '9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('FFFFFF');
      $column++;
  }

    // Ajouter les données
    $row = 10;
    $fill = false;
    foreach ($entrepots as $entrepot) {
        // Gérer les stocks associés à l'entrepôt
        $stocks = $entrepot->getStocks();
        $stocksList = [];
        foreach ($stocks as $stock) {
            $stocksList[] = $stock->getProduit()->getNom() ?? 'Stock #' . $stock->getId();
        }
        $stocksString = empty($stocksList) ? 'N/A' : implode(', ', $stocksList);

        $fillColor = $fill ? 'F5F7FA' : 'FFFFFF'; // Gris clair / Blanc
        $range = 'A' . $row . ':F' . $row;
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fillColor);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

        $sheet->setCellValue('A' . $row, $entrepot->getNom());
        $sheet->setCellValue('B' . $row, $entrepot->getAdresse());
        $sheet->setCellValue('C' . $row, $entrepot->getVille() ?? 'N/A');
        $sheet->setCellValue('D' . $row, $entrepot->getEspace());
        $sheet->setCellValue('E' . $row, $stocksString);

        $sheet->getStyle($range)->getFont()->setSize(10)->getColor()->setRGB('212121'); // Gris foncé
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        $fill = !$fill;
    }

    // Ajuster la largeur des colonnes
    foreach (range('A', 'E') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    $sheet->setCellValue('A' . ($row + 1), '© 2025 Agriplaner - Généré automatiquement');
    $sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));
    $sheet->getStyle('A' . ($row + 1))->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('787878');
    $sheet->getStyle('A' . ($row + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Création du fichier Excel en mémoire
    $writer = new Xlsx($spreadsheet);
    $tempFilePath = tempnam(sys_get_temp_dir(), 'entrepot_') . '.xlsx';
    $writer->save($tempFilePath);

    // Vérifier que le fichier a été correctement créé
    if (!file_exists($tempFilePath) || filesize($tempFilePath) === 0) {
        throw new \Exception('Erreur lors de la génération du fichier Excel.');
    }

    // Retourner le fichier en réponse HTTP
    return new BinaryFileResponse($tempFilePath, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="entrepot_export_' . date('Y-m-d') . '.xlsx"',
    ], true, 'inline', true, true);
}
}