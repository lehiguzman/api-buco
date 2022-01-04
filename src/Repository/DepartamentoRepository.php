<?php

namespace App\Repository;

use App\Entity\Departamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Departamento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Departamento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Departamento[]    findAll()
 * @method Departamento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartamentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Departamento::class);
    }

    /**
     * Obtiene todos los registros de Departamento
     * 
     * @return array
     */
    public function findDepartamentos(array $params = null)
    {
        $orderField = 'd.id';
        $orderSort = 'ASC';
        $limit = 0;
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

        $dql = "SELECT d FROM App:Departamento d 
                WHERE (d.eliminado = 0 OR d.eliminado IS NULL) $querySearch
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

    public function findDepartamentosSistema($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT d FROM App:Departamento d                                     
                                    WHERE d.sistemaTipo = :id
                                    AND (d.eliminado = :not_eliminated OR d.eliminado IS NULL)                                     
                                    ORDER BY d.id ASC")
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
    public function findDepartamentoActivo($id)
    {
        return $this->createQueryBuilder('d')
            ->where('d.eliminado = :not_eliminated OR d.eliminado IS NULL')
            ->andWhere('d.id = :id')
            ->setParameters(array('not_eliminated' => 0, 'id' => intval($id)))
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Obtiene el departamento según el nombre
     *
     * @return array
     */
    public function departamentoNombre($nombre, $id = 0)
    {

        $dql = "SELECT d FROM App:Departamento d 
                WHERE LOWER(TRIM(d.nombre)) = LOWER(:nombre) AND d.id != :id";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters(array(
            'nombre' => $nombre,
            'id' => $id
        ));
        return $query->getResult();
    }
}
