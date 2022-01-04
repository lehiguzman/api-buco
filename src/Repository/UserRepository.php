<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obtiene todos los usuarios del sistema
     * 
     * @return array
     */
    public function findUsers(array $params = null)
    {
        $orderField = 'u.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'u.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (u._name LIKE :search OR u._email LIKE :search OR u._username LIKE :search)";
        }

        $dql = "SELECT u FROM App:User u WHERE (u.eliminado = 0 OR u.eliminado IS NULL) $querySearch
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
    public function findUserActivo($id)
    {
        return $this->createQueryBuilder('u')
            ->where('u.eliminado = 0 OR u.eliminado IS NULL')
            ->andWhere('u.id = :id')
            ->setParameter('id', intval($id))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene los Push Tokens del Usuario
     *
     * @return object
     */
    public function findUserPushTokens($userID)
    {
        $dql = "SELECT f FROM App:Firebase f
               INNER JOIN App:User u WITH u.id = f.user
               WHERE u.eliminado = 0 AND u.isActive = 1 AND u.id = $userID";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }
}
