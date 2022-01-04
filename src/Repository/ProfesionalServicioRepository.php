<?php

namespace App\Repository;

use App\Entity\ProfesionalServicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfesionalServicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfesionalServicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfesionalServicio[]    findAll()
 * @method ProfesionalServicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfesionalServicio::class);
    }

    public function findbyProfesional($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT ps FROM App:ProfesionalServicio ps 
                                    JOIN ps.profesional p 
                                    JOIN ps.servicio s 
                                    WHERE ps.profesional = :id 
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY p.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    // /**
    //  * @return ProfesionalServicio[] Returns an array of ProfesionalServicio objects
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
    public function findOneBySomeField($value): ?ProfesionalServicio
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
