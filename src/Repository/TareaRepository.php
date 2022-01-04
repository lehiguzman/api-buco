<?php

namespace App\Repository;

use App\Entity\Tarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tarea|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tarea|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tarea[]    findAll()
 * @method Tarea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarea::class);
    }

    /**
     * Obtiene todos los registros de Tarea
     * 
     * @return array
     */
    public function findTareas(array $params = null)
    {
        $orderField = 't.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 't.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (t.nombre LIKE :search)";
        }

        $dql = "SELECT t FROM App:Tarea t 
                WHERE (t.eliminado = 0 OR t.eliminado IS NULL) $querySearch
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
    public function findTareaActiva($id)
    {
        return $this->createQueryBuilder('t')
            ->where('t.eliminado = :not_eliminated OR t.eliminado IS NULL')
            ->andWhere('t.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene la tarea según el nombre y el servicio
     *
     * @return array
     */
    public function tareaNombre($nombre, $servicioID, $id = 0)
    {
        $dql = "SELECT t FROM App:Tarea t 
                WHERE LOWER(TRIM(t.nombre)) = LOWER(:nombre) AND t.servicio = :servicioID AND t.id != :id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre'     => $nombre,
            'servicioID' => $servicioID,
            'id'         => $id
        ));
        return $query->getResult();
    }
}
