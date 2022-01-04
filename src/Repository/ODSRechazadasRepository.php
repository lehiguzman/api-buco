<?php

namespace App\Repository;

use App\Entity\ODSRechazadas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ODSRechazadas|null find($id, $lockMode = null, $lockVersion = null)
 * @method ODSRechazadas|null findOneBy(array $criteria, array $orderBy = null)
 * @method ODSRechazadas[]    findAll()
 * @method ODSRechazadas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ODSRechazadasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ODSRechazadas::class);
    }

    /**
     * @return ODSRechazadas[] Returns an array of ODSRechazadas objects
     */
    public function findByProfesional($profesionalID)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.profesional = :profesionalID')
            ->andWhere('o.estado = 0')
            ->setParameter('profesionalID', $profesionalID)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?ODSRechazadas
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
