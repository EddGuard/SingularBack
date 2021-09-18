<?php

namespace App\Repository;

use App\Entity\ActiveRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActiveRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActiveRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActiveRecord[]    findAll()
 * @method ActiveRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveRecord::class);
    }

    /**
     * @param $value
     * @return ActiveRecord|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneById($value): ?ActiveRecord
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    // /**
    //  * @return ActiveRecord[] Returns an array of ActiveRecord objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActiveRecord
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
