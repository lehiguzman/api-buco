<?php

namespace App\Repository;

use App\Entity\Firebase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Firebase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Firebase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Firebase[]    findAll()
 * @method Firebase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirebaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Firebase::class);
    }

    // /**
    //  * @return Firebase[] Returns an array of Firebase objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Firebase
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
