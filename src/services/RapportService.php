<?php
namespace App\Service;

use Symfony\Component\Process\Process;
use App\services\RapportExportService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class RapportService
{
    private RapportExportService $rapportExportService;

    public function __construct(RapportExportService $rapportExportService)
    {
        $this->rapportExportService = $rapportExportService;
    }

    public function generateReport(string $prompt): string
    {
        // Chemin vers le script Python
        $scriptPath = '/path/to/your/script.py';

        // Exécution du script Python
        $process = new Process(['python', $scriptPath, $prompt]);
        $process->run();

        // Récupération du rapport généré
        $report = $process->getOutput();

        return $report;
    }

    #[Route('/dashboard', name: 'generate_report')]
    public function exportData(): Response
    {
        $this->rapportExportService->exportToCSV();
        return new Response('Données exportées avec succès.');
    }
}