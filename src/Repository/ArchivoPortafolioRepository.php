<?php

namespace App\Repository;

use App\Entity\ArchivoPortafolio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArchivoPortafolio|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchivoPortafolio|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchivoPortafolio[]    findAll()
 * @method ArchivoPortafolio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchivoPortafolioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchivoPortafolio::class);
    }

    /**
     * Obtiene todos los registros de ArchivoPortafolio
     * 
     * @return array
     */
    public function findArchivoPortafolios(array $params = null)
    {
        $orderField = 'a.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'a.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (a.nombre LIKE :search)";
        }

        $dql = "SELECT a FROM App:ArchivoPortafolio a
                WHERE (a.eliminado = 0 OR a.eliminado IS NULL) $querySearch
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
    public function findArchivoPortafolioActivo($id)
    {
        return $this->createQueryBuilder('a')
            ->where('a.eliminado = :not_eliminated OR a.eliminado IS NULL')
            ->andWhere('a.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

     /**
     * @param integer $id
     *
     * @return array
     */
    public function findArchivoPortafolioByProfesional($id)
    {
        return $this->createQueryBuilder('a')
            ->where('a.eliminado = :not_eliminated OR a.eliminado IS NULL')
            ->andWhere('a.profesional = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getResult();
    }

    // /**
    //  * @return ArchivoPortafolio[] Returns an array of ArchivoPortafolio objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ArchivoPortafolio
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
