<?php

// src/Command/ExportDataCommand.php
namespace App\Command;

use App\Service\DataExtractorService;
use App\Service\DataExporterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Intl\Scripts;

class ExportDataCommand extends Command
{
    protected static $defaultName = 'app:export-data';

    private $dataExtractor;
    private $dataExporter;

    public function __construct(DataExtractorService $dataExtractor, DataExporterService $dataExporter)
    {
        parent::__construct();
        $this->dataExtractor = $dataExtractor;
        $this->dataExporter = $dataExporter;
    }

    protected function configure()
    {
        $this
            ->setDescription('Exporte les données de vente et de stock vers un fichier CSV.')
            ->setHelp('Cette commande exporte les données de vente et de stock dans des fichiers CSV.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Exporter les données de vente
        $salesData = $this->dataExtractor->getSalesData();
        $this->dataExporter->exportToCsv($salesData, 'Scripts\sales_data.csv');
        $output->writeln('Données de vente exportées avec succès dans sales_data.csv.');

        // Exporter les données de stock
        $stockData = $this->dataExtractor->getStockData();
        $this->dataExporter->exportToCsv($stockData, 'Scripts\stock_data.csv');
        $output->writeln('Données de stock exportées avec succès dans stock_data.csv.');

        return Command::SUCCESS;
    }
}