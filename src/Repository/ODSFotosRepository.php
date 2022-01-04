<?php

namespace App\Repository;

use App\Entity\ODSFotos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ODSFotos|null find($id, $lockMode = null, $lockVersion = null)
 * @method ODSFotos|null findOneBy(array $criteria, array $orderBy = null)
 * @method ODSFotos[]    findAll()
 * @method ODSFotos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ODSFotosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ODSFotos::class);
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findByODS($id)
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.ordenServicio', 'os')
            ->where('os.id = :id')
            ->setParameter('id', intval($id))
            ->getQuery()->getResult();
    }

}
