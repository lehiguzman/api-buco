<?php

namespace App\Repository;

use App\Entity\TipoTarifa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoTarifa|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoTarifa|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoTarifa[]    findAll()
 * @method TipoTarifa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoTarifaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoTarifa::class);
    }

    /**
     * Obtiene todos los registros de TipoTarifa
     * 
     * @return array
     */
    public function findTiposTarifa(array $params = null)
    {
        $orderField = 'tt.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'tt.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (tt.nombre LIKE :search)";
        }

        $dql = "SELECT tt FROM App:TipoTarifa tt 
                WHERE (tt.eliminado = 0 OR tt.eliminado IS NULL) $querySearch
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
    public function findTipoTarifaActivo($id)
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.eliminado = :not_eliminated OR tt.eliminado IS NULL')
            ->andWhere('tt.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene el tipo de tarifa según el nombre
     *
     * @return array
     */
    public function tipoTarifaNombre($nombre, $id = 0)
    {

        $dql = "SELECT tt FROM App:TipoTarifa tt 
                WHERE LOWER(TRIM(tt.nombre)) = LOWER(:nombre) AND tt.id != :id AND (tt.eliminado = :not_eliminated OR tt.eliminado IS NULL)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre' => $nombre,
            'id' => $id,
            'not_eliminated' => 0
        ));
        return $query->getResult();
    }
}
