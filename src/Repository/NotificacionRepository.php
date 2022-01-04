<?php

namespace App\Repository;

use App\Entity\Notificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notificacion[]    findAll()
 * @method Notificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notificacion::class);
    }

    /**
     * @param integer $userId
     *
     * @return array
     */
    public function findNotificacionesByUser($userId)
    {
        return $this->createQueryBuilder('n')
            ->where('n.eliminado = :not_eliminated OR n.eliminado IS NULL')
            ->andWhere('n.user = :userId')
            ->setParameters(array('not_eliminated' => 0, 'userId' => intval($userId)))
            ->getQuery()->getResult();
    }

    /**
     * Obtiene todos los registros de Notificacion
     * 
     * @return array
     */
    public function findNotificaciones(array $params = null)
    {
        $orderField = 'n.leido';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'n.' . $params['field'];
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

        $dql = "SELECT n FROM App:Notificacion n INNER JOIN App:User u WITH u.id = n.user
                WHERE (n.eliminado = 0 OR n.eliminado IS NULL) 
                AND (u.eliminado = 0 OR u.eliminado IS NULL) 
                AND u.isActive = 1
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
    public function findNotificacion($id)
    {
        return $this->createQueryBuilder('n')
            ->where('n.eliminado = :not_eliminated OR n.eliminado IS NULL')
            ->andWhere('n.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }
}
