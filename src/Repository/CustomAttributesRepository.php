<?php

namespace App\Repository;

use App\Entity\CustomAttributes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CustomAttributes|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomAttributes|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomAttributes[]    findAll()
 * @method CustomAttributes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomAttributesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomAttributes::class);
    }

    // /**
    //  * @return CustomAttributes[] Returns an array of CustomAttributes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomAttributes
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
