<?php

namespace App\Repository;

use App\Entity\ServicioTipoDocumento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServicioTipoDocumento|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServicioTipoDocumento|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServicioTipoDocumento[]    findAll()
 * @method ServicioTipoDocumento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioTipoDocumentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicioTipoDocumento::class);
    }

    public function findbyService($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT std FROM App:ServicioTipoDocumento std 
                                    JOIN std.tipoDocumento td 
                                    JOIN std.servicio s 
                                    WHERE std.servicio = :id 
                                    AND (td.eliminado = :not_eliminated OR td.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY std.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyDocumentType($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT std FROM App:ServicioTipoDocumento std 
                                    JOIN std.servicio s 
                                    JOIN std.tipoDocumento td 
                                    WHERE std.tipoDocumento = :id 
                                    AND (td.eliminado = :not_eliminated OR td.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY std.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyServiceDocumentType()
    {

        $em = $this->getEntityManager();

        /*$query = $em->createQuery("SELECT std FROM App:ServicioTipoDocumento std 
                                    JOIN std.servicio s
                                    JOIN std.tipoDocumento td                                     
                                    WHERE (td.eliminado = :not_eliminated OR td.eliminado IS NULL) 
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL) 
                                    ORDER BY std.id ASC")
            ->setParameters(array(                
                'not_eliminated' => 0
            ));
        $results = $query->getResult();*/
        return $this->createQueryBuilder('std')        
        ->leftJoin('std.servicio', 's')
        ->leftJoin('std.tipoDocumento', 'td')
        ->where('td.eliminado = :not_eliminated OR td.eliminado IS NULL')
        ->andWhere('s.eliminado = :not_eliminated OR s.eliminado IS NULL')        
        ->setParameter('not_eliminated', 0)        
        ->groupBy('std.servicio')
        ->getQuery()
        ->getResult();

        //---- Respuesta ----
        //return $results;
    }
}
