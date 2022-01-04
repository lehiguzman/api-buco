<?php

namespace App\Repository;

use App\Entity\TipoDocumento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoDocumento|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoDocumento|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoDocumento[]    findAll()
 * @method TipoDocumento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoDocumentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoDocumento::class);
    }

    /**
     * Obtiene todos los registros de TipoDocumento
     * 
     * @return array
     */
    public function findTiposDocumento(array $params = null)
    {
        $orderField = 'td.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'td.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (td.nombre LIKE :search)";
        }

        $dql = "SELECT td FROM App:TipoDocumento td 
                WHERE (td.eliminado = 0 OR td.eliminado IS NULL) $querySearch
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
    public function findTipoDocumentoActivo($id)
    {
        return $this->createQueryBuilder('td')
            ->where('td.eliminado = :not_eliminated OR td.eliminado IS NULL')
            ->andWhere('td.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene el tipo de documento según el nombre
     *
     * @return array
     */
    public function tipoDocumentoNombre($nombre, $id = 0)
    {

        $dql = "SELECT td FROM App:TipoDocumento td 
                WHERE LOWER(TRIM(td.nombre)) = LOWER(:nombre) AND td.id != :id AND (td.eliminado = :not_eliminated OR td.eliminado IS NULL)";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre' => $nombre,
            'id' => $id,
            'not_eliminated' => 0
        ));
        return $query->getResult();
    }
}
