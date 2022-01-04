<?php

namespace App\Repository;

use App\Entity\OrdenServicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrdenServicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdenServicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdenServicio[]    findAll()
 * @method OrdenServicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdenServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdenServicio::class);
    }

    /**
     * Obtiene todos los registros de OrdenServicio
     * 
     * @return array
     */
    public function findOrdenesServicio(array $params = null)
    {
        $orderField = 'os.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $searchProfesional = "";
        $querySearch = "";
        $parameters = array();

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'os.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }
        if (key_exists('searchProfesional', $params)) {
            $searchProfesional = $params['searchProfesional'];
        }

        if (trim($search) == true) {
            $querySearch .= " AND (u.name LIKE :search)";
            $parameters['search'] = '%' . $search . '%';
        }

        if (trim($searchProfesional) == true) {
            $querySearch .= " AND (p.nombre LIKE :searchProfesional OR p.apellido LIKE :searchProfesional)";
            $parameters['searchProfesional'] = '%' . $searchProfesional . '%';
        }

        if (key_exists('findByEstatus', $params)) {
            $querySearch .= " AND os.estatus = :estatus";
            $parameters['estatus'] = $params['findByEstatus'];
        }

        if (key_exists('findByFechas', $params)) {
            $querySearch .= " AND os.fechaHora BETWEEN :desde AND :hasta";
            $parameters['desde'] = $params['desde'];
            $parameters['hasta'] = $params['hasta'];
        }

        $dql = "SELECT os FROM App:OrdenServicio os 
                INNER JOIN App:User u WITH u.id = os.user 
                INNER JOIN App:Profesional p WITH p.id = os.profesional 
                WHERE (os.eliminado = 0 OR os.eliminado IS NULL) AND (u.eliminado = 0 OR u.eliminado IS NULL) $querySearch
                ORDER BY $orderField $orderSort";
        $query = $this->getEntityManager()->createQuery($dql);
        if (count($parameters)) {
            $query->setParameters($parameters);
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
    public function findOrdenServicioClienteActivo($id)
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->where('o.eliminado = :not_eliminated OR o.eliminado IS NULL')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->andWhere('u.id = :userId')
            ->setParameters(array(
                'not_eliminated' => 0,
                'userId' => intval($id)
            ))
            ->orderBy('u.id', 'ASC')
            ->getQuery()->getResult();
    }    

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findOrdenServicioActivo($id)
    {
        return $this->createQueryBuilder('o')
            ->where('o.eliminado = :not_eliminated OR o.eliminado IS NULL')
            ->andWhere('o.id = :id')
            ->setParameters(array(
                'not_eliminated' => 0,
                'id' => intval($id)
            ))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param integer $id
     * 
     * Método para las Push Notifications
     *
     * @return array
     */
    public function findODSArray($id)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT ods FROM App:OrdenServicio ods WHERE ods.id = :id ";
        return $em->createQuery($dql)->setParameter('id', $id)->getArrayResult();
    }

    /**
     * @param integer $id
     * 
     * Método para las Fotos de una ODS
     *
     * @return array
     */
    public function findODSFotos($id)
    {
        $em = $this->getEntityManager();       

        $dql = "SELECT f FROM App:ODSFotos f JOIN f.ordenServicio ods WHERE ods.id = :id";
        $query = $em->createQuery($dql)->setParameter('id', $id);
        $fotos = $query->getArrayResult();

        return $fotos;
    }    
}
