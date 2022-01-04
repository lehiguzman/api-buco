<?php

namespace App\Repository;

use App\Entity\Profesional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Profesional|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profesional|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profesional[]    findAll()
 * @method Profesional[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profesional::class);
    }

    public function findbyDistancia($latitud, $longitud)
    {
        $em = $this->getEntityManager();

        //---- Query ----
        $dql = "SELECT 
                    a.id,
                    a.nombre,
                    a.apellido,

                        ( 6371 * acos(cos(radians($latitud)) * cos(radians(a.latitud)) * cos(radians(a.longitud) - radians($longitud)) + sin(radians($latitud)) * sin(radians(a.latitud)))) as distancia

                    FROM App:Profesional a
                    WHERE a.estatus = 1
                        AND (a.latitud IS NOT NULL AND a.longitud IS NOT NULL)
                        AND ( 6371 * acos(cos(radians($latitud)) * cos(radians(a.latitud)) * cos(radians(a.longitud) - radians($longitud)) + sin(radians($latitud)) * sin(radians(a.latitud)))) < a.radioCobertura
                    ORDER BY distancia ASC";


        $query = $em->createQuery($dql);
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findProfesionalByDistancia($id, $latitud, $longitud)
    {
        $em = $this->getEntityManager();

        //---- Query ----
        $dql = "SELECT 
                    a.id,
                    a.nombre,
                    a.apellido,

                        ( 6371 * acos(cos(radians($latitud)) * cos(radians(a.latitud)) * cos(radians(a.longitud) - radians($longitud)) + sin(radians($latitud)) * sin(radians(a.latitud)))) as distancia

                    FROM App:Profesional a
                    WHERE a.estatus = 1
                        AND a.id = $id
                        AND (a.latitud IS NOT NULL AND a.longitud IS NOT NULL)
                        
                    ORDER BY distancia ASC";


        $query = $em->createQuery($dql);
        $results = $query->getOneOrNullResult();

        //---- Respuesta ----
        return $results;
    }

    public function findByCalificacion()
    {
        $em = $this->getEntityManager();

        //---- Query ----
        $dql = "SELECT 
                    a.id,
                    a.nombre,
                    a.apellido,

                    a.promedioPuntualidad,
                    a.promedioServicio,
                    a.promedioPresencia,
                    a.promedioConocimiento,                    

                    ((a.promedioPuntualidad+a.promedioServicio+a.promedioPresencia+a.promedioConocimiento)/5) as promedioGeneral 

                    FROM App:Profesional a
                    WHERE a.estatus = 1
                    ORDER BY promedioGeneral DESC";


        $query = $em->createQuery($dql);
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findProfesionalByCalificacion($id)
    {
        $em = $this->getEntityManager();

        //---- Query ----
        $dql = "SELECT 
                    a.id,
                    a.nombre,
                    a.apellido,

                    a.promedioPuntualidad,
                    a.promedioServicio,
                    a.promedioPresencia,
                    a.promedioConocimiento,
                    a.promedioRecomendado,               

                    ((a.promedioPuntualidad+
                      a.promedioServicio+
                      a.promedioPresencia+
                      a.promedioConocimiento+
                      a.promedioRecomendado)/5) as promedioGeneral 

                    FROM App:Profesional a
                    WHERE a.estatus = 1 AND
                    a.id = $id
                    ORDER BY promedioGeneral DESC";


        $query = $em->createQuery($dql);
        $results = $query->getOneOrNullResult();

        //---- Respuesta ----
        return $results;
    }

    /**
     * Obtiene todos los registros de Profesional
     * 
     * @return array
     */
    public function findProfesionales(array $params = null)
    {
        $orderField = 'p.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'p.' . $params['field'];
            if ($params['field'] === 'correo') {
                $orderField = 'u.email';
            }
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (p.nombre LIKE :search OR p.apellido LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
        }

        $dql = "SELECT p FROM App:Profesional p INNER JOIN App:User u WITH u.id = p.user
                WHERE (p.eliminado = 0 OR p.eliminado IS NULL) AND (u.eliminado = 0 OR u.eliminado IS NULL) AND u.isActive = 1 $querySearch
                ORDER BY $orderField $orderSort";
        $query = $this->getEntityManager()->createQuery($dql);
        if (trim($search) == true) {
            $query->setParameter('search', '%' . $search . '%');
        }
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findByServicio($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.servicio', 's')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('s.eliminado = :not_eliminated OR s.eliminado IS NULL')
            ->andWhere('s.id = :id')
            ->andWhere('p.estatus = 1')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findProfesionalActivo($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->andWhere('p.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findProfesionalUserActivo($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->andWhere('u.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param integer $userId
     *
     * @return array
     */
    public function findByUserActivo($userId)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('p.eliminado = :not_eliminated OR p.eliminado IS NULL')
            ->andWhere('u.id = :id')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($userId)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Profesionales con 3 o más ODS Rechazadas
     * 
     * @return array
     */
    public function findProfesionalesODSRechazadas()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT prof FROM App:Profesional prof
        WHERE prof.eliminado = 0 AND prof.ordenesRechazadas >= 3
          AND prof.fechaPenalizadoInicio IS NULL AND prof.fechaPenalizadoFin IS NULL";
        $query = $em->createQuery($dql);
        $results = $query->getResult();

        return $results;
    }
}
