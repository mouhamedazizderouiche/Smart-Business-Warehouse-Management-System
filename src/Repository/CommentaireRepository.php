<?php

namespace App\Repository;

use App\Entity\Commentaire;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    public function getAverageRatingByProduit(Produit $produit): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('AVG(c.note)')
            ->where('c.produit = :produit')
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }
}