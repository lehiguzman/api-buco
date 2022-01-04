<?php

namespace App\Repository;

use App\Entity\Comision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comision|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comision|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comision[]    findAll()
 * @method Comision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comision::class);
    }

    /**
     * Obtiene todos los registros de Comision
     * 
     * @return array
     */
    public function findComisiones(array $params = null)
    {
        $orderField = 'c.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'c.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (c.nombre LIKE :search)";
        }

        $dql = "SELECT c FROM App:Comision c 
                WHERE (c.eliminado = 0 OR c.eliminado IS NULL) 
                $querySearch
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
    public function findComisionActiva($id)
    {
        return $this->createQueryBuilder('c')
            ->where('c.eliminado = :not_eliminated OR c.eliminado IS NULL')
            ->andWhere('c.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene la comisión según el nombre
     *
     * @return array
     */
    public function comisionNombre($nombre, $id = 0)
    {

        $dql = "SELECT c FROM App:Comision c 
                WHERE LOWER(TRIM(c.nombre)) = LOWER(:nombre) 
                AND c.id != :id 
                AND (c.eliminado = :not_eliminated OR c.eliminado IS NULL)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre'         => $nombre,
            'id'             => $id,
            'not_eliminated' => 0
        ));
        return $query->getResult();
    }
}
