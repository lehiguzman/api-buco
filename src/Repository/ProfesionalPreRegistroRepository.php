<?php

namespace App\Repository;

use App\Entity\ProfesionalPreRegistro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfesionalPreRegistro|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfesionalPreRegistro|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfesionalPreRegistro[]    findAll()
 * @method ProfesionalPreRegistro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfesionalPreRegistroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfesionalPreRegistro::class);
    }

    /**
     * Obtiene tlos pre-registros completados del profesional
     * 
     * @return object[]
     */
    public function findPreRegistros($estado = 2, array $params = null)
    {
        $orderField = 'preReg.fechaActualizado';
        $orderSort = 'DESC';
        $limit = 150;
        $search = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'preReg.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        $query = $this->createQueryBuilder('preReg');
        if (trim($search) == true) {
            $query->where('(preReg.nombreCompleto LIKE :search OR preReg.cedula LIKE :search OR preReg.correo LIKE :search)');
            $query->setParameter('search', '%' . $search . '%');
        } else {
            $query->where('preReg.estado = :estado');
            $query->setParameter('estado', $estado);
        }
        $query->orderBy($orderField, $orderSort);

        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        return $query->getQuery()->getResult();
    }


    // /**
    //  * @return ProfesionalPreRegistro[] Returns an array of ProfesionalPreRegistro objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProfesionalPreRegistro
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
