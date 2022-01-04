<?php

namespace App\Repository;

use App\Entity\FormularioDinamico;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormularioDinamico|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormularioDinamico|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormularioDinamico[]    findAll()
 * @method FormularioDinamico[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormularioDinamicoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormularioDinamico::class);
    }

    // /**
    //  * @return FormularioDinamico[] Returns an array of FormularioDinamico objects
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
    public function findOneBySomeField($value): ?FormularioDinamico
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
