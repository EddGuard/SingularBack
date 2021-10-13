<?php

namespace App\Repository;

use App\Entity\AttributeValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AttributeValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttributeValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttributeValue[]    findAll()
 * @method AttributeValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttributeValue::class);
    }

    public function deleteOrphanedAttributes()
    {
        $sql = "DELETE FROM attribute_value av WHERE av.active_basics_id IS NULL
                                 AND av.active_customs_id IS NULL
                                 AND av.active_type_basics_id IS NULL
                                 AND av.active_type_customs_id IS NULL;";
        $em = $this->getEntityManager();
        try {
            $statement = $em->getConnection()->prepare($sql);
            $statement->execute();
            return $statement->fetchAll();
        } catch (Exception $e) {
        } catch (\Doctrine\DBAL\Exception $e) {
        }
        return 0;
    }

    // /**
    //  * @return AttributeValue[] Returns an array of AttributeValue objects
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
    public function findOneBySomeField($value): ?AttributeValue
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
