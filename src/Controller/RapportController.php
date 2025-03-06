<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\services\RapportExportService;

class RapportController extends AbstractController
{
    private RapportExportService $rapportExportService;

    public function __construct(RapportExportService $rapportExportService)
    {
        $this->rapportExportService = $rapportExportService;
    }

// Exemple dans un contrôleur
#[Route('/export-csv', name: 'export_csv')]
public function exportCSV(RapportExportService $rapportExportService): Response
{
    $rapportExportService->exportToCSV();
    return new Response('Rapport CSV généré avec succès.');
}}