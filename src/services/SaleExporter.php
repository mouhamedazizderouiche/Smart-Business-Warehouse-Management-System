<?php
namespace App\Service;

use App\Entity\Commande;
use App\Entity\CommandeFinalisee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class SaleExporter
{
    private $entityManager;
    private $filesystem;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $filesystem)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
    }

    public function exportToCsv(string $filePath): void
    {
        // Récupérer les commandes finalisées
        $commandesFinalisees = $this->entityManager->getRepository(CommandeFinalisee::class)->findAll();

        // Récupérer les commandes (panier)
        $commandes = $this->entityManager->getRepository(Commande::class)->findAll();

        // Ouvrir le fichier CSV en mode écriture
        $file = fopen($filePath, 'w');

        // Écrire l'en-tête du fichier CSV
        fputcsv($file, ['date', 'product_id', 'quantity']);

        // Écrire les données des commandes finalisées
        foreach ($commandesFinalisees as $commande) {
          fputcsv($file, [
              $commande->getDateCommande()->format('Y-m-d'), // Format de la date
              $commande->getProduitId()->__toString(), // Convertir l'UUID en chaîne
              $commande->getQuantite(), // Quantité vendue
          ]);
      }
  
      // Écrire les données des commandes (panier)
      foreach ($commandes as $commande) {
          fputcsv($file, [
              $commande->getDateCommande()->format('Y-m-d'), // Format de la date
              $commande->getProduit()->getId()->__toString(), // Convertir l'UUID en chaîne
              $commande->getQuantite(), // Quantité vendue
          ]);
      }
      // Fermer le fichier CSV
        fclose($file);
    }
}