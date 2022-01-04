<?php

namespace App\Repository;

use App\Entity\Documento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Documento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Documento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Documento[]    findAll()
 * @method Documento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Documento::class);
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findActiveDocsByProfesional($id)
    {

        $dql = "SELECT d FROM App:Documento d
                INNER JOIN App:ProfesionalServicio ps WITH ps.profesional = d.profesional
                INNER JOIN App:TipoDocumento tp WITH tp.id = d.tipoDocumento
                INNER JOIN App:ServicioTipoDocumento stp WITH stp.tipoDocumento = tp.id
                INNER JOIN App:Servicio s WITH s.id = stp.servicio
                WHERE d.profesional = :id ORDER BY s.id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array('id' => $id));
        return $query->getResult();

        /*return $this->createQueryBuilder('d')
            ->where('d.eliminado = :not_eliminated OR d.eliminado IS NULL')
            ->andWhere('d.profesional = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getResult();*/
    }

    /**
     * Obtiene todos los registros de Documento
     * 
     * @return array
     */
    public function findDocumentos(array $params = null)
    {
        $orderField = 'd.id';
        $orderSort = 'ASC';
        $limit = 50;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 'd.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (d.nombre LIKE :search)";
        }

        $dql = "SELECT d FROM App:Documento d 
                WHERE d.eliminado = 0 $querySearch
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
    public function findDocumentoActivo($id)
    {
        return $this->createQueryBuilder('d')
            ->where('d.eliminado = :not_eliminated OR d.eliminado IS NULL')
            ->andWhere('d.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene el documento según el nombre y profesional
     *
     * @return array
     */
    public function documentoNombre($nombre, $profesional_id, $id = 0)
    {

        $dql = "SELECT d FROM App:Documento d INNER JOIN App:Profesional p WITH p.id = d.profesional
                WHERE LOWER(TRIM(d.nombre)) = LOWER(:nombre) 
                AND p.id = :profesional_id 
                AND d.id != :id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre'         => $nombre,
            'profesional_id' => $profesional_id,
            'id'             => $id
        ));
        return $query->getResult();
    }
}
