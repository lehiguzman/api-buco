<?php

namespace App\Repository;

use App\Entity\FirebaseTokens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirebaseTokens|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirebaseTokens|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirebaseTokens[]    findAll()
 * @method FirebaseTokens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirebaseTokensRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FirebaseTokens::class);
    }

    // /**
    //  * @return FirebaseTokens[] Returns an array of FirebaseTokens objects
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
    public function findOneBySomeField($value): ?FirebaseTokens
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
