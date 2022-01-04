<?php

namespace App\Repository;

use App\Entity\MetodoPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetodoPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetodoPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetodoPago[]    findAll()
 * @method MetodoPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetodoPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetodoPago::class);
    }

    /**
     * Obtiene todos los registros de MetodoPago
     * 
     * @return array
     */
    public function findMetodosPago(array $params = null)
    {
        $orderField = 'mp.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'mp.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (mp.nombre LIKE :search)";
        }

        $dql = "SELECT mp FROM App:MetodoPago mp 
                WHERE mp.eliminado = 0 $querySearch
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
     * Obtiene el método de pago según el nombre
     *
     * @return array
     */
    public function MetodoPagoNombre($nombre, $id = 0)
    {

        $dql = "SELECT mp FROM App:MetodoPago mp 
                WHERE LOWER(TRIM(mp.nombre)) = LOWER(:nombre) AND mp.id != :id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre' => $nombre,
            'id' => $id
        ));
        return $query->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findMetodoPagoActivo($id)
    {
        return $this->createQueryBuilder('mp')
            ->where('mp.eliminado = :not_eliminated OR mp.eliminado IS NULL')
            ->andWhere('mp.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }
}
