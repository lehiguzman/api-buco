<?php

namespace App\Repository;

use App\Entity\ProfesionalMetodoPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfesionalMetodoPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfesionalMetodoPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfesionalMetodoPago[]    findAll()
 * @method ProfesionalMetodoPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalMetodoPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfesionalMetodoPago::class);
    }

    public function findbyProfesional($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT mp FROM App:ProfesionalMetodoPago mp
                                    JOIN mp.metodoPago m 
                                    JOIN mp.profesional p 
                                    WHERE mp.profesional = :id 
                                    AND (m.eliminado = :not_eliminated OR m.eliminado IS NULL) 
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    ORDER BY mp.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    // /**
    //  * @return ProfesionalMetodoPago[] Returns an array of ProfesionalMetodoPago objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfesionalMetodoPago
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
