<?php

namespace App\Repository;

use App\Entity\ProfesionalTarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfesionalTarea|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfesionalTarea|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfesionalTarea[]    findAll()
 * @method ProfesionalTarea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalTareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfesionalTarea::class);
    }

    public function findbyProfessional($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT pt FROM App:ProfesionalTarea pt 
                                    JOIN pt.tarea t 
                                    JOIN pt.profesional p                                     
                                    WHERE pt.profesional = :id                                      
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY t.servicio ASC")        
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyProfesionalServicio($id, $idServicio)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT pt FROM App:ProfesionalTarea pt 
                                    JOIN pt.tarea t 
                                    JOIN pt.profesional p                                     
                                    WHERE pt.profesional = :id
                                    AND t.servicio = :idServicio
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY t.servicio ASC")        
            ->setParameters(array(
                'id' => intval($id),
                'idServicio' => intval($idServicio),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyProfesionalService($profesional_id, $servicio_id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT pt FROM App:ProfesionalTarea pt 
                                    JOIN pt.tarea t 
                                    JOIN pt.profesional p 
                                    WHERE pt.profesional = :profesional_id 
                                    AND t.servicio = :servicio_id 
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY pt.id ASC")
            ->setParameters(array(
                'profesional_id' => intval($profesional_id),
                'servicio_id' => intval($servicio_id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyTask($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT pt FROM App:ProfesionalTarea pt 
                                    JOIN pt.tarea t 
                                    JOIN pt.profesional p 
                                    WHERE pt.tarea = :id 
                                    AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY pt.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }
}
