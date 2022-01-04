<?php

namespace App\Repository;

use App\Entity\FormularioDinamicoServicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormularioDinamicoServicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormularioDinamicoServicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormularioDinamicoServicio[]    findAll()
 * @method FormularioDinamicoServicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormularioDinamicoServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormularioDinamicoServicio::class);
    }

    /**
     * Obtiene todos los registros de Fomulario Dinamico Servicio
     * 
     * @return object[]
     */
    public function findFomularioDinamicoServicios(array $params = null)
    {
        $orderField = 'fdS.servicio';
        $orderSort = 'ASC';
        $limit = 200;
        $search = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'fdS.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        $query = $this->createQueryBuilder('fdS');
        if (trim($search) == true) {
            $query->where('(fdS.servicio = :search OR fdS.nombre LIKE :search)');
            $query->setParameter('search', '%' . $search . '%');
        } else {
            $query->where('fdS.eliminado = 0');
        }
        $query->orderBy($orderField, $orderSort);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Obtiene todos los Campos Especiales del Servicio
     * 
     * @return object[]
     */
    public function findFomularioDinamicoServicioID($servicioID)
    {
        $query = $this->createQueryBuilder('fdS');
        $query->where('fdS.servicio = :servicioID');
        $query->andWhere('fdS.eliminado = 0');
        $query->setParameter('servicioID', $servicioID);

        return $query->getQuery()->getResult();
    }

    /**
     * @param integer $id
     *
     * @return object
     */
    public function findFormularioDinamicoServicioActivo($id, $servicioID)
    {
        return $this->createQueryBuilder('fdS')
            ->where('fdS.eliminado = 0')
            ->andWhere('fdS.servicio = :servicioID')
            ->andWhere('fdS.id = :id')
            ->setParameter('id', $id)
            ->setParameter('servicioID', $servicioID)
            ->getQuery()->getOneOrNullResult();
    }


    // /**
    //  * @return FormularioDinamicoServicio[] Returns an array of FormularioDinamicoServicio objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FormularioDinamicoServicio
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
