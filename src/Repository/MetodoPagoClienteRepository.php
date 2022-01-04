<?php

namespace App\Repository;

use App\Entity\MetodoPagoCliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MetodoPagoCliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method MetodoPagoCliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method MetodoPagoCliente[]    findAll()
 * @method MetodoPagoCliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MetodoPagoClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MetodoPagoCliente::class);
    }

    /**
     * Obtiene todos los métodos de pago de un cliente
     * 
     * @return array
     */
    public function findMetodosPagoCliente(array $params = null)
    {
        $orderField = 'mpc.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'mpc.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND mpc.nombre LIKE :search";
        }

        $dql = "SELECT mpc FROM App:MetodoPagoCliente mpc WHERE mpc.status = 1 $querySearch
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
    public function findMetodoPagoClienteActivo($userId)
    {
        return $this->createQueryBuilder('mpc')
            ->leftJoin('mpc.user', 'u')
            ->where('mpc.eliminado = :not_eliminated OR mpc.eliminado IS NULL')
            ->andWhere('u.eliminado = :not_eliminated OR u.eliminado IS NULL')
            ->andWhere('u.id = :userId')
            ->setParameters(array(
                'not_eliminated' => 0,
                'userId' => intval($userId)
            ))
            ->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findMetodoPagoActivo($id)
    {
        return $this->createQueryBuilder('mpc')
            ->where('mpc.eliminado = :not_eliminated OR mpc.eliminado IS NULL')
            ->andWhere('mpc.id = :id')
            ->setParameters(array(
                'not_eliminated' => 0,
                'id' => intval($id)
            ))
            ->getQuery()->getOneOrNullResult();
    }
}
