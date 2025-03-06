<?php
namespace App\services;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;

class RapportExportService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function exportToCSV()
    {
        // Construire la requête sans utiliser CommandeFinalisee
        $query = $this->entityManager->getRepository(Commande::class)
            ->createQueryBuilder('c')
            ->join('c.produit', 'p') // Jointure avec l'entité Produit
            ->join('p.stocks', 's') // Jointure avec l'entité Stock
            ->join('s.entrepots', 'e') // Jointure avec l'entité Entrepot
            ->select(
                'c.id as idCommande',
                'c.dateCommande',
                'c.quantite as quantiteCommandee',
                'p.id as idProduit',
                'p.nom as nomProduit',
                'p.prixUnitaire',
                's.quantite as quantiteStock',
                's.dateEntree as dateEntreeStock',
                'e.nom as nomEntrepot'
            )
            ->getQuery()
            ->getResult();

        // Créer le fichier CSV
        $fp = fopen('donnees.csv', 'w');

        // En-tête du CSV
        fputcsv($fp, [
            'ID Commande',
            'Date Commande',
            'Quantité Commandée',
            'ID Produit',
            'Nom Produit',
            'Prix Unitaire',
            'Quantité Stock',
            'Date Entrée Stock',
            'Nom Entrepot'
        ]);

        // Remplir le CSV avec les données
        foreach ($query as $row) {
            fputcsv($fp, [
                $row['idCommande'],
                $row['dateCommande']->format('Y-m-d H:i:s'),
                $row['quantiteCommandee'],
                $row['idProduit'],
                $row['nomProduit'],
                $row['prixUnitaire'],
                $row['quantiteStock'],
                $row['dateEntreeStock']->format('Y-m-d H:i:s'),
                $row['nomEntrepot']
            ]);
        }

        fclose($fp);
    }
}