<?php
namespace App\Controller;

use App\Service\SaleExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Stock;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ExportController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/export-sales', name: 'export_sales', methods: ['GET'])]
    public function exportSales(SaleExporter $saleExporter): Response
    {
        // Chemin du fichier CSV
        $filePath = $this->getParameter('kernel.project_dir') . '/scripts/sales_data.csv';

        // Exporter les données en CSV
        $saleExporter->exportToCsv($filePath);

        // Récupérer les données de stock depuis la base de données
        $stockData = $this->entityManager->getRepository(Stock::class)->findAll();
        $stockLevels = [];
        foreach ($stockData as $stock) {
            $stockLevels[$stock->getProduit()->getId()->toRfc4122()] = $stock->getSeuilAlert();
        }

        // Exécuter le script Python
        $output = shell_exec("python " . $this->getParameter('kernel.project_dir') . "/scripts/predict_demand.py");

        // Convertir les résultats JSON en tableau PHP
        $recommendations = json_decode($output, true);

        // Afficher les résultats dans un template Twig
        return $this->render('demand/prediction.html.twig', [
            'recommendations' => $recommendations,
        ]);
    }
}