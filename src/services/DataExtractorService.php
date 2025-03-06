<?php
namespace App\Service;

use App\Repository\CommandeFinaliseeRepository;
use App\Repository\StockRepository;

class DataExtractorService
{
    private $commandeFinaliseeRepository;
    private $stockRepository;

    public function __construct(
        CommandeFinaliseeRepository $commandeFinaliseeRepository,
        StockRepository $stockRepository
    ) {
        $this->commandeFinaliseeRepository = $commandeFinaliseeRepository;
        $this->stockRepository = $stockRepository;
    }

// src/Service/DataExtractorService.php
// src/Service/DataExtractorService.php
public function getSalesData(): array
{
    $ventes = $this->commandeFinaliseeRepository->findAll();
    $data = [];

    foreach ($ventes as $vente) {
        $data[] = [
            'date' => $vente->getDateCommande()->format('Y-m-d'),
            'produit' => $vente->getNomProduit(),
            'quantite' => $vente->getQuantite(),
            'prix' => $vente->getProduitPrix(),
        ];
    }

    // Inspecter les données
    dump($data);

    return $data;
}   // src/Service/DataExtractorService.php
public function getStockData(): array
{
    $stocks = $this->stockRepository->findAll();
    $data = [];

    foreach ($stocks as $stock) {
        $data[] = [
            'produit' => $stock->getProduit()->getNom(),
            'quantite' => $stock->getProduit()->getQuantite(),
            'date_entree' => $stock->getDateEntree()->format('Y-m-d'),
            'date_sortie' => $stock->getDateSortie() ? $stock->getDateSortie()->format('Y-m-d') : null,
        ];
    }

    return $data;
}
}