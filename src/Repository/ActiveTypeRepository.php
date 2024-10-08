<?php

namespace App\Repository;

use App\Entity\ActiveType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActiveType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActiveType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActiveType[]    findAll()
 * @method ActiveType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveType::class);
    }

    public function getActivesCountByTypeId(int $id){
        {
            $sql = "SELECT count(a.id) 
FROM active_type t 
LEFT JOIN active a on t.id = a.active_type_id 
WHERE t.id = :id;";
            $em = $this->getEntityManager();
            try {
                $statement = $em->getConnection()->prepare($sql);
                $statement->bindParam('id', $id);
                $statement->execute();
                return $statement->fetchOne();
            } catch (Exception $e) {
            } catch (\Doctrine\DBAL\Exception $e) {
            }
            return 0;
        }
    }

    // /**
    //  * @return ActiveType[] Returns an array of ActiveType objects
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
    public function findOneBySomeField($value): ?ActiveType
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
