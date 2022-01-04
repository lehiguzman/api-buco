<?php

namespace App\Repository;

use App\Entity\Direccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Direccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Direccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Direccion[]    findAll()
 * @method Direccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DireccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Direccion::class);
    }

    /**
     * Obtiene todos los registros de Servicio
     * 
     * @return array
     */
    public function findDirecciones(array $params = null)
    {
        $orderField = 'd.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'd.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (u.name LIKE :search)";
        }

        $dql = "SELECT d FROM App:Direccion d INNER JOIN App:User u WITH u.id = d.user 
                WHERE (d.eliminado = 0 OR d.eliminado IS NULL) 
                AND (u.eliminado = 0 OR u.eliminado IS NULL) 
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
    public function findDireccionActivo($id)
    {
        return $this->createQueryBuilder('d')
            ->where('d.eliminado = :not_eliminated OR d.eliminado IS NULL')
            ->andWhere('d.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findDireccionUser($id)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.user', 'u')
            ->where('d.eliminado = :not_eliminated OR d.eliminado IS NULL')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->andWhere('u.id = :userId')
            ->setParameters(array('not_eliminated' => 0, 'userId' => intval($id)))
            ->getQuery()->getResult();
    }
}
