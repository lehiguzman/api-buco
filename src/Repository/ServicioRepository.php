<?php

namespace App\Repository;

use App\Entity\Servicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Servicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Servicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Servicio[]    findAll()
 * @method Servicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Servicio::class);
    }

    /**
     * Obtiene todos los registros de Servicio
     * 
     * @return array
     */
    public function findServicios(array $params = null)
    {
        $orderField = 's.id';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";

        if (key_exists('sort', $params)) {
            $orderSort = $params['sort'];
        }
        if (key_exists('field', $params)) {
            $orderField = 's.' . $params['field'];
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (s.nombre LIKE :search)";
        }

        $dql = "SELECT s FROM App:Servicio s 
                WHERE (s.eliminado = 0 OR s.eliminado IS NULL) $querySearch
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

    public function findServiciosSistema($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT s FROM App:Servicio s                                     
                                    WHERE s.sistemaTipo = :id
                                    AND (s.eliminado = :not_eliminated OR s.eliminado IS NULL)                                     
                                    ORDER BY s.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findServicioActivo($id)
    {
        return $this->createQueryBuilder('s')
            ->where('s.eliminado = :not_eliminated OR s.eliminado IS NULL')
            ->andWhere('s.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene el servicio según el nombre
     *
     * @return array
     */
    public function servicioNombre($nombre, $id = 0)
    {

        $dql = "SELECT s FROM App:Servicio s 
                WHERE LOWER(TRIM(s.nombre)) = LOWER(:nombre) AND s.id != :id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre' => $nombre,
            'id' => $id
        ));
        return $query->getResult();
    }
}
