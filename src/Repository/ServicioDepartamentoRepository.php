<?php

namespace App\Repository;

use App\Entity\ServicioDepartamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicioDepartamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicioDepartamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicioDepartamento[]    findAll()
 * @method ServicioDepartamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioDepartamentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicioDepartamento::class);
    }

    public function findbyService($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT sd FROM App:ServicioDepartamento sd 
                                    JOIN sd.departamento d 
                                    JOIN sd.servicio s 
                                    WHERE sd.servicio = :id 
                                    AND (d.eliminado = :not_eliminated OR d.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY sd.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyDepartment($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT sd FROM App:ServicioDepartamento sd 
                                    JOIN sd.servicio s 
                                    JOIN sd.departamento d 
                                    WHERE sd.departamento = :id 
                                    AND s.estatus = 1
                                    AND (d.eliminado = :not_eliminated OR d.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY sd.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }
}
