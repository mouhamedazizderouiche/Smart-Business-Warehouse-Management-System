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

    // Initialiser TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Définir les informations du document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Votre Application');
    $pdf->SetTitle('Export des Entrepôts');
    $pdf->SetSubject('Export des Entrepôts');
    $pdf->SetKeywords('Entrepôt, PDF, Export');

    // Ajouter une page
    $pdf->AddPage();

    // Ajouter un logo
    $logoPath = __DIR__ . '/../../public/uploads/photos/logo.jpg'; // Chemin relatif correct
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 10, 10, 30, 0, 'JPG'); // Position (x=10, y=10), largeur=30, hauteur automatique
    }

    // Ajouter un en-tête
    $pdf->SetFont('helvetica', 'B', 16); // Police en gras, taille 16
    $pdf->Cell(0, 10, 'Rapport des Entrepôts', 0, 1, 'C'); // Titre centré
    $pdf->SetFont('helvetica', 'I', 10); // Police en italique, taille 10
    $pdf->Cell(0, 10, 'Généré le : ' . date('d/m/Y à H:i'), 0, 1, 'C'); // Date centrée
    $pdf->Ln(10); // Saut de ligne

    // Définir les largeurs des colonnes
    $columnWidths = [
        'nom' => 40,
        'adresse' => 50,
        'ville' => 30,
        'espace' => 30,
        'stocks' => 50,
    ];

    // Ajouter l'en-tête du tableau avec des couleurs
    $pdf->SetFillColor(200, 220, 255); // Couleur de fond des en-têtes
    $pdf->SetTextColor(0, 0, 0); // Couleur du texte
    $pdf->SetFont('helvetica', 'B', 12); // Police en gras, taille 12

    $pdf->Cell($columnWidths['nom'], 10, 'Nom', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['adresse'], 10, 'Adresse', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['ville'], 10, 'Ville', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['espace'], 10, 'Espace (m²)', 1, 0, 'C', true);
    $pdf->Cell($columnWidths['stocks'], 10, 'Stocks', 1, 1, 'C', true); // Saut de ligne après cette cellule

    // Ajouter les données des entrepôts
    $pdf->SetFont('helvetica', '', 10); // Police normale, taille 10
    $pdf->SetFillColor(255, 255, 255); // Couleur de fond des cellules de données
    $pdf->SetTextColor(0, 0, 0); // Couleur du texte

    foreach ($entrepots as $entrepot) {
        // Gérer les stocks associés à l'entrepôt
        $stocks = $entrepot->getStocks();
        $stocksList = [];
        foreach ($stocks as $stock) {
            $stocksList[] = $stock->getProduit()->getNom() ?? 'Stock #' . $stock->getId();
        }
        $stocksString = empty($stocksList) ? 'N/A' : implode(', ', $stocksList);

        // Ajouter les cellules au PDF
        $pdf->Cell($columnWidths['nom'], 10, $entrepot->getNom(), 1, 0, 'L');
        $pdf->Cell($columnWidths['adresse'], 10, $entrepot->getAdresse(), 1, 0, 'C');
        $pdf->Cell($columnWidths['ville'], 10, $entrepot->getVille() ?? 'N/A', 1, 0, 'C');
        $pdf->Cell($columnWidths['espace'], 10, $entrepot->getEspace(), 1, 0, 'C');
        $pdf->Cell($columnWidths['stocks'], 10, $stocksString, 1, 1, 'C'); // Saut de ligne après cette cellule
    }

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

    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajouter un logo si disponible
    $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/logo.jpg';
    if (file_exists($logoPath)) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);
    }

    // Ajouter un en-tête
    $sheet->mergeCells('B2:F2');
    $sheet->setCellValue('B2', 'Rapport des Entrepôts');
    $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Ajouter la date de génération
    $sheet->mergeCells('B3:F3');
    $sheet->setCellValue('B3', 'Généré le : ' . date('d/m/Y à H:i'));
    $sheet->getStyle('B3')->getFont()->setItalic(true)->setSize(10);
    $sheet->getStyle('B3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Ajouter les en-têtes de colonnes
    $headers = ['Nom', 'Adresse', 'Ville', 'Espace (m²)', 'Stocks'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '5', $header);
        $sheet->getStyle($column . '5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($column . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4F81BD');
        $sheet->getStyle($column . '5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $column++;
    }

    // Ajouter les données
    $row = 6;
    foreach ($entrepots as $entrepot) {
        // Gérer les stocks associés à l'entrepôt
        $stocks = $entrepot->getStocks();
        $stocksList = [];
        foreach ($stocks as $stock) {
            $stocksList[] = $stock->getProduit()->getNom() ?? 'Stock #' . $stock->getId();
        }
        $stocksString = empty($stocksList) ? 'N/A' : implode(', ', $stocksList);

        // Ajouter les données dans les cellules
        $sheet->setCellValue('A' . $row, $entrepot->getNom());
        $sheet->setCellValue('B' . $row, $entrepot->getAdresse());
        $sheet->setCellValue('C' . $row, $entrepot->getVille() ?? 'N/A');
        $sheet->setCellValue('D' . $row, $entrepot->getEspace());
        $sheet->setCellValue('E' . $row, $stocksString);

        $row++;
    }

    // Ajuster la largeur des colonnes
    foreach (range('A', 'E') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

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