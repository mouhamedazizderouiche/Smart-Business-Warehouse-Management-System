<?php
// scripts/test_export.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Kernel;
use App\Service\SaleExporter;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv(__DIR__ . '/../../.env');

// Initialiser le kernel Symfony
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

// Récupérer le service SaleExporter
$container = $kernel->getContainer();
$saleExporter = $container->get(SaleExporter::class);

// Exporter les données en CSV
$filePath = __DIR__ . '/sales_data.csv';
$saleExporter->exportToCsv($filePath);

echo "Données exportées avec succès dans $filePath !\n";