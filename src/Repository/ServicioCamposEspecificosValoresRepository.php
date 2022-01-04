<?php

namespace App\Repository;

use App\Entity\ServicioCamposEspecificosValores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicioCamposEspecificosValores|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicioCamposEspecificosValores|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicioCamposEspecificosValores[]    findAll()
 * @method ServicioCamposEspecificosValores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioCamposEspecificosValoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicioCamposEspecificosValores::class);
    }

    // /**
    //  * @return ServicioCamposEspecificosValores[] Returns an array of ServicioCamposEspecificosValores objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ServicioCamposEspecificosValores
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
