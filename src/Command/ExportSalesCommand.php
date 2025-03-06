<?php
namespace App\Command;

use App\Service\SaleExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportSalesCommand extends Command
{
    protected static $defaultName = 'export-sales';
    private $saleExporter;

    public function __construct(SaleExporter $saleExporter)
    {
        parent::__construct();
        $this->saleExporter = $saleExporter;
    }

    protected function configure()
    {
        $this
            ->setDescription('Exporte les données de ventes vers un fichier CSV.')
            ->setHelp('Cette commande exporte les données de ventes dans un fichier CSV.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Chemin du fichier CSV
        $filePath = $this->getApplication()->getKernel()->getProjectDir() . '/scripts/sales_data.csv';

        // Exporter les données en CSV
        $this->saleExporter->exportToCsv($filePath);

        $output->writeln('Données exportées avec succès dans ' . $filePath);

        return Command::SUCCESS;
    }
}