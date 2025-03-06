<?php

namespace App\Repository;

use App\Entity\Entrepot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entrepot>
 */
class EntrepotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entrepot::class);
    }

    public function getActiveWarehousesCount(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

  }