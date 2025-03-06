<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 *
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Trouve les événements par type.
     *
     * @param string $type Le type d'événement à filtrer.
     * @return Evenement[] Retourne un tableau d'objets Evenement.
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
    public function findBySearchAndSort(string $searchTerm = '', string $sortBy = 'default'): array
{
    $queryBuilder = $this->createQueryBuilder('e');

    // Ajouter une condition de recherche si un terme est fourni
    if ($searchTerm) {
        $queryBuilder->andWhere('e.titre LIKE :searchTerm OR e.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    // Ajouter un tri en fonction du paramètre
    switch ($sortBy) {
        case 'foire':
            $queryBuilder->andWhere('e.type = :type')
                ->setParameter('type', 'foire');
            break;
        case 'formation':
            $queryBuilder->andWhere('e.type = :type')
                ->setParameter('type', 'formation');
            break;
        case 'conference':
            $queryBuilder->andWhere('e.type = :type')
                ->setParameter('type', 'conference');
            break;
        case 'date':
            $queryBuilder->orderBy('e.dateDebut', 'ASC');
            break;
        default:
            // Pas de tri spécifique
            break;
    }

    return $queryBuilder->getQuery()->getResult();
}

    /**
     * Trouve les événements triés par date de début (ascendant ou descendant).
     *
     * @param string $order L'ordre de tri ('ASC' ou 'DESC').
     * @return Evenement[] Retourne un tableau d'objets Evenement.
     */
    public function findAllOrderedByDate(string $order = 'ASC'): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.dateDebut', $order)
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode pour filtrer par un champ spécifique (à adapter selon vos besoins)
    // /**
    //  * @return Evenement[] Returns an array of Evenement objects
    //  */
    // public function findByExampleField($value): array
    // {
    //     return $this->createQueryBuilder('e')
    //         ->andWhere('e.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('e.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult();
    // }

    // Exemple de méthode pour trouver un événement par un champ spécifique (à adapter selon vos besoins)
    // public function findOneBySomeField($value): ?Evenement
    // {
    //     return $this->createQueryBuilder('e')
    //         ->andWhere('e.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
}