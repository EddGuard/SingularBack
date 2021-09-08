<?php

namespace App\Repository;

use App\Entity\BasicAttributes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BasicAttributes|null find($id, $lockMode = null, $lockVersion = null)
 * @method BasicAttributes|null findOneBy(array $criteria, array $orderBy = null)
 * @method BasicAttributes[]    findAll()
 * @method BasicAttributes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasicAttributesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BasicAttributes::class);
    }

    // /**
    //  * @return BasicAttributes[] Returns an array of BasicAttributes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BasicAttributes
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
