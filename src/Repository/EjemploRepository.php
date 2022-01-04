<?php

namespace App\Repository;

use App\Entity\Ejemplo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ejemplo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ejemplo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ejemplo[]    findAll()
 * @method Ejemplo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EjemploRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ejemplo::class);
    }

    /**
     * Obtiene todos los registros de ejemplo
     * 
     * @return object[]
     */
    public function findEjemplos(array $params = null)
    {
        $orderField = 'e.id';
        $orderSort = 'DESC';
        $limit = 150;
        $search = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'e.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        $query = $this->createQueryBuilder('e');
        if (trim($search) == true) {
            $query->where('(e.campo1 LIKE :search OR e.campo2 LIKE :search)');
            $query->setParameter('search', '%' . $search . '%');
        } else {
            $query->where('e.eliminado = 0');
        }
        $query->orderBy($orderField, $orderSort);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return object
     */
    public function findEjemploActivo($id)
    {
        return $this->createQueryBuilder('e')
            ->where('e.eliminado = 0')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }


    // /**
    //  * @return Ejemplo[] Returns an array of Ejemplo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ejemplo
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
