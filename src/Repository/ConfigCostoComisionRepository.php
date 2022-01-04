<?php

namespace App\Repository;

use App\Entity\ConfigCostoComision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConfigCostoComision|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigCostoComision|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigCostoComision[]    findAll()
 * @method ConfigCostoComision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigCostoComisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigCostoComision::class);
    }

    // /**
    //  * @return ConfigCostoComision[] Returns an array of ConfigCostoComision objects
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
    public function findOneBySomeField($value): ?ConfigCostoComision
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
