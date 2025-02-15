<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function save(Commande $commande, bool $flush = true): void
    {
        $this->_em->persist($commande);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Commande $commande, bool $flush = true): void
    {
        $this->_em->remove($commande);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByProduit($produitId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.produit = :produitId')
            ->setParameter('produitId', $produitId)
            ->getQuery()
            ->getResult();
    }
}
