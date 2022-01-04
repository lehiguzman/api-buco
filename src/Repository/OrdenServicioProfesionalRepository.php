<?php

namespace App\Repository;

use App\Entity\OrdenServicioProfesional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrdenServicioProfesional|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdenServicioProfesional|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdenServicioProfesional[]    findAll()
 * @method OrdenServicioProfesional[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdenServicioProfesionalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdenServicioProfesional::class);
    }

    public function findbyODS($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT op FROM App:OrdenServicioProfesional op 
                                    JOIN op.profesional p 
                                    JOIN op.ordenServicio o 
                                    WHERE op.ordenServicio = :id 
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (o.eliminado = :not_eliminated OR o.eliminado IS NULL) 
                                    ORDER BY o.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findOrdenServicioProfesionalActivo($id)
    {
        return $this->createQueryBuilder('osp')
            ->leftJoin('osp.profesional', 'p')
            ->leftJoin('osp.ordenServicio', 'os')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('os.eliminado = :not_eliminated OR os.eliminado IS NULL')
            ->andWhere('p.id = :profesionalId')
            ->setParameters(array(
                'not_eliminated' => 0,
                'profesionalId' => intval($id)
            ))
            ->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findByUser($id)
    {
        return $this->createQueryBuilder('osp')
            ->leftJoin('osp.profesional', 'p')
            ->leftJoin('osp.ordenServicio', 'os')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('os.eliminado = :not_eliminated OR os.eliminado IS NULL')            
            ->setParameters(array(
                'not_eliminated' => 0                
            ))
            ->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findDisponibilidad($id, $fechaHoraDesde, $fechaHoraHasta)
    {
        return $this->createQueryBuilder('osp')
            ->leftJoin('osp.profesional', 'p')
            ->leftJoin('osp.ordenServicio', 'os')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')            
            ->andWhere('p.id = :id')
            ->andWhere('os.fechaHora >= :fechaHoraDesde')
            ->andWhere('os.fechaHora <= :fechaHoraHasta')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id), 'fechaHoraDesde' => $fechaHoraDesde, 'fechaHoraHasta' => $fechaHoraHasta))
            ->getQuery()->getOneOrNullResult();
    }



    // /**
    //  * @return OrdenServicioProfesional[] Returns an array of OrdenServicioProfesional objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrdenServicioProfesional
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
