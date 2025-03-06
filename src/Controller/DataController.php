<?php
// src/Controller/DataController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\DataExtractorService;
use App\Service\DataExporterService;

class DataController extends AbstractController
{
    public function exportData(
        DataExtractorService $dataExtractor,
        DataExporterService $dataExporter
    ): Response {
        $salesData = $dataExtractor->getSalesData();
        $stockData = $dataExtractor->getStockData();

        $dataExporter->exportToCsv($salesData, 'scripts\sales_data.csv');
        $dataExporter->exportToCsv($stockData, 'scripts\stock_data.csv');

        return new Response('Données exportées avec succès !');
    }
}