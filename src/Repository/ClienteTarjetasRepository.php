<?php

namespace App\Repository;

use App\Entity\ClienteTarjetas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClienteTarjetas|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClienteTarjetas|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClienteTarjetas[]    findAll()
 * @method ClienteTarjetas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClienteTarjetasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClienteTarjetas::class);
    }

    // /**
    //  * @return ClienteTarjetas[] Returns an array of ClienteTarjetas objects
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
    public function findOneBySomeField($value): ?ClienteTarjetas
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
