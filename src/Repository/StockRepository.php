<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTotalStocks(int $days = 7): int
    {
        $today = new \DateTime('today');
        $startDate = (clone $today)->modify("-" . ($days - 1) . " days");

        return (int) $this->createQueryBuilder('s')
            ->select('SUM(p.quantite)')
            ->leftJoin('s.produit', 'p')
            ->where('s.date_entree >= :start')
            ->setParameter('start', $startDate->setTime(0, 0, 0))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAlertsCount(int $days = 7): int
    {
        $today = new \DateTime('today');
        $startDate = (clone $today)->modify("-" . ($days - 1) . " days");

        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->leftJoin('s.produit', 'p')
            ->where('p.quantite <= s.seuil_alert + 1')
            ->andWhere('p.quantite IS NOT NULL')
            ->andWhere('s.seuil_alert IS NOT NULL')
            ->andWhere('s.date_entree >= :start')
            ->setParameter('start', $startDate->setTime(0, 0, 0))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getStockTrend(string $timeRange = '7'): array
    {
        $today = new \DateTime('today');
        $startDate = (clone $today);
    
        // Définir la période en fonction de la plage de temps sélectionnée
        switch ($timeRange) {
            case '7':
                $startDate->modify('-7 days');
                break;
            case '30':
                $startDate->modify('-30 days');
                break;
            case '31':
                $startDate->modify('-1 month');
                break;
            case '90':
                $startDate->modify('-90 days');
                break;
            case '365':
                $startDate->modify('-1 year');
                break;
            default:
                $startDate->modify('-7 days');
                break;
        }
    
        // Récupérer le stock total actuel
        $currentTotal = $this->getTotalStocks($timeRange);
    
        // Récupérer le stock total pour la période précédente
        $previousStartDate = (clone $startDate)->modify('-' . $timeRange . ' days');
        $previousTotal = $this->createQueryBuilder('s')
            ->select('SUM(p.quantite)')
            ->leftJoin('s.produit', 'p')
            ->where('s.date_entree >= :previousStart')
            ->andWhere('s.date_entree < :start')
            ->setParameter('previousStart', $previousStartDate->setTime(0, 0, 0))
            ->setParameter('start', $startDate->setTime(0, 0, 0))
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    
        // Calcul de la tendance
        if ($previousTotal > 0) {
            $trend = (($currentTotal - $previousTotal) / $previousTotal) * 100;
        } else {
            $trend = $currentTotal > 0 ? 100 : 0; // Si la période précédente était 0, tendance = 100% si actuel > 0
        }
    
        return [
            'current' => $currentTotal,
            'previous' => $previousTotal,
            'trend' => round($trend, 1),
            'direction' => $trend >= 0 ? 'up' : 'down',
        ];
    }
    public function getStockDistribution(int $days = 7): array
    {
        $today = new \DateTime('today');
        $startDate = (clone $today)->modify("-" . ($days - 1) . " days");

        $results = $this->createQueryBuilder('s')
            ->select('p.nom AS productName, SUM(p.quantite) AS quantity')
            ->leftJoin('s.produit', 'p')
            ->where('s.date_entree >= :start')
            ->setParameter('start', $startDate->setTime(0, 0, 0))
            ->groupBy('p.id, p.nom')
            ->getQuery()
            ->getArrayResult();

        $labels = [];
        $quantities = [];
        foreach ($results as $result) {
            $labels[] = $result['productName'] ?? 'Produit inconnu';
            $quantities[] = (int) $result['quantity'];
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities
        ];
    }

    public function getStockMovement(int $days = 7): array
    {
        $today = new \DateTime('today');
        $startDate = (clone $today)->modify("-" . ($days - 1) . " days");

        $results = $this->createQueryBuilder('s')
            ->select("s.date_entree AS date, SUM(p.quantite) AS quantity")
            ->leftJoin('s.produit', 'p')
            ->where('s.date_entree >= :start')
            ->setParameter('start', $startDate->setTime(0, 0, 0))
            ->groupBy('s.date_entree')
            ->orderBy('s.date_entree', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $labels = [];
        $quantities = [];
        $cumulative = 0;

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (clone $today)->modify("-$i days");
            $dateStr = $date->format('Y-m-d');
            $labels[] = "J-" . $i;

            $found = false;
            foreach ($results as $result) {
                if (is_string($result['date'])) {
                    $resultDate = (new \DateTime($result['date']))->format('Y-m-d');
                } else {
                    $resultDate = $result['date']->format('Y-m-d');
                }

                if ($resultDate === $dateStr) {
                    $cumulative += (int) $result['quantity'];
                    $quantities[] = $cumulative;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $quantities[] = $cumulative;
            }
        }

        return [
            'labels' => $labels,
            'quantities' => $quantities
        ];
    }
    public function getTotalStockss(string $timeRange = '7'): int
{
    $date = new \DateTime();
    switch ($timeRange) {
        case '7':
            $date->modify('-7 days');
            break;
        case '30':
            $date->modify('-30 days');
            break;
        case '31':
            $date->modify('-1 month');
            break;
        case '90':
            $date->modify('-90 days');
            break;
        case '365':
            $date->modify('-1 year');
            break;
        default:
            $date->modify('-7 days');
            break;
    }

    return $this->createQueryBuilder('s')
        ->select('SUM(p.quantite)')
        ->leftJoin('s.produit', 'p')
        ->where('s.date_entree >= :date')
        ->setParameter('date', $date)
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}
}
