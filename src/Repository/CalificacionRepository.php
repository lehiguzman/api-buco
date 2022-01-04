<?php

namespace App\Repository;

use App\Entity\Calificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Calificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calificacion[]    findAll()
 * @method Calificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calificacion::class);
    }

    /**
     * Obtiene todos los registros de Calificacion
     * 
     * @return array
     */
    public function findCalificaciones(array $params = array())
    {

        $orderField = 'nombre';
        $orderSort = 'ASC';
        $limit = 0;
        $search = "";
        $querySearch = "";
        $parameters["not_eliminated"] = 0;

        if (key_exists('sort', $params)) {
            $orderSort = strtoupper($params['sort']);
            $orderSort = ($orderSort == 'ASC' || $orderSort == 'DESC') ? $orderSort : 'ASC';
        }
        if (key_exists('field', $params)) {
            if (in_array($params['field'], array('puntualidad', 'servicio', 'presencia', 'conocimiento', 'recomendado'))) {
                $orderField = 'promedio_' . $params['field'];
            }
        }
        if (key_exists('limit', $params)) {
            $limit = $params['limit'];
        }
        if (key_exists('search', $params)) {
            $search = $params['search'];
        }

        if (trim($search) == true) {
            $querySearch = " AND (p.nombre LIKE :search OR p.apellido LIKE :search)";
            $parameters['search'] = '%' . $search . '%';
        }

        if (key_exists('findByServicio', $params)) {
            $querySearch .= " AND s.id = :servicio_id";
            $parameters['servicio_id'] = $params['findByServicio'];
        }

        $dql = "SELECT 
                    c.id,
                    p.id as profesional_id,
                    p.nombre as profesional_nombre,
                    p.apellido as profesional_apellido,
                    u.foto as profesional_foto,  
                    c.puntualidad,
                    c.servicio,
                    c.presencia,
                    c.conocimiento,
                    c.recomendado,
                    ((c.puntualidad+c.servicio+c.presencia+c.conocimiento+c.recomendado)/5) as promedio
                FROM App:Calificacion c 
                INNER JOIN App:OrdenServicio os WITH os.id = c.ordenServicio 
                INNER JOIN App:OrdenServicioProfesional osp WITH os.id = osp.ordenServicio 
                INNER JOIN App:Servicio s WITH s.id = os.servicio 
                INNER JOIN App:Profesional p WITH p.id = osp.profesional 
                INNER JOIN App:User u WITH u.id = p.user
                WHERE (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                AND (u.eliminado = :not_eliminated OR u.eliminado IS NULL) 
                $querySearch";
        $query = $this->getEntityManager()->createQuery($dql);
        if (count($parameters)) {
            $query->setParameters($parameters);
        }

        /*return array('sql' => $query->getSQL(),
                     'parameters' => $query->getParameters());*/

        $calificaciones = $query->getResult();

        $profesionales_valorados = $this->promediarValoraciones($calificaciones, $orderField, $orderSort, $limit);

        return $profesionales_valorados;
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findByOS($orden_servicio_id)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.ordenServicio', 'os')
            ->andWhere('os.id = :ordenServicioId')
            ->setParameter('ordenServicioId', intval($orden_servicio_id))
            ->getQuery()->getResult();
    }

    public function findCalificacionesRango($inicio, $fin)
    {

        $em = $this->getEntityManager();

        $dql = "SELECT 
                    c.id,
                    p.id as profesional_id,
                    p.nombre as profesional_nombre,
                    p.apellido as profesional_apellido,
                    u.foto as profesional_foto,
                    c.puntualidad,
                    c.servicio,
                    c.presencia,
                    c.conocimiento,
                    c.recomendado,
                    ((c.puntualidad+c.servicio+c.presencia+c.conocimiento+c.recomendado)/5) as promedio
                FROM App:Calificacion c 
                INNER JOIN App:OrdenServicio os WITH os.id = c.ordenServicio 
                INNER JOIN App:Profesional p WITH p.id = os.profesional 
                INNER JOIN App:User u WITH u.id = p.user
                WHERE (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                AND (u.eliminado = :not_eliminated OR u.eliminado IS NULL) 
                AND c.fechaCreacion BETWEEN :inicio AND :fin";

        $query = $em->createQuery($dql);
        $query->setParameters(array(
            'not_eliminated' => 0,
            'inicio' => $inicio,
            'fin'    => $fin
        ));
        $calificaciones = $query->getResult();

        $profesionales_valorados = $this->promediarValoraciones($calificaciones, 'promedio_general', 'DESC', 0);

        return $profesionales_valorados;
    }

    public function findCalificacionesProfesional($profesional_id, $servicio_id, $desde, $hasta)
    {

        $em = $this->getEntityManager();
        $parameters = array();
        $parameters['profesional_id'] = $profesional_id;
        $querySearch = "";

        if ($servicio_id) {
            $querySearch .= " AND s.id = :servicio_id";
            $parameters['servicio_id'] = $servicio_id;
        }

        if ($desde && $hasta) {
            $querySearch .= " AND c.fechaCreacion BETWEEN :inicio AND :fin";
            $parameters['inicio'] = $desde;
            $parameters['fin'] = $hasta;
        }

        $dql = "SELECT 
                    c.id,
                    os.id as ordenServicio_id,
                    p.id as profesional_id,
                    p.nombre as profesional_nombre,
                    p.apellido as profesional_apellido,
                    u.foto as profesional_foto,
                    u.name as user_nombre,
                    c.puntualidad,
                    c.servicio,
                    c.presencia,
                    c.conocimiento,
                    c.recomendado,
                    ((c.puntualidad+c.servicio+c.presencia+c.conocimiento+c.recomendado)/5) as promedio, 
                    c.fechaCreacion,
                    c.comentarios 
                FROM App:Calificacion c 
                INNER JOIN App:OrdenServicio os WITH os.id = c.ordenServicio
                INNER JOIN App:OrdenServicioProfesional osp WITH os.id = osp.ordenServicio 
                INNER JOIN App:Profesional p WITH p.id = osp.profesional 
                INNER JOIN App:Servicio s WITH s.id = os.servicio 
                INNER JOIN App:User u WITH u.id = os.user 
                WHERE p.id = :profesional_id $querySearch 
                ORDER BY c.id ASC";

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    public function sortBySubArrayValue($array, $key, $dir = 'ASC')
    {

        $sorter = array();
        $rebuilt = array();

        //make sure we start at the beginning of $array
        reset($array);

        //loop through the $array and store the $key's value
        foreach ($array as $ii => $value) {
            $sorter[$ii] = $value[$key];
        }

        //sort the built array of key values
        if ($dir == 'ASC') asort($sorter);
        if ($dir == 'DESC') arsort($sorter);

        //build the returning array and add the other values associated with the key
        foreach ($sorter as $ii => $value) {
            $rebuilt[$ii] = $array[$ii];
        }

        //assign the rebuilt array to $array
        $array = $rebuilt;

        return $array;
    }

    public function promediarValoraciones($calificaciones, $orderField, $orderSort, $limit)
    {

        $profesionales_valorados = array();

        foreach ($calificaciones as $calificacion) {

            if (!array_key_exists($calificacion["profesional_id"], $profesionales_valorados)) {
                $profesionales_valorados[$calificacion["profesional_id"]] = array(
                    'id'                     => $calificacion["profesional_id"],
                    'nombre'                 => $calificacion["profesional_nombre"],
                    'apellido'               => $calificacion["profesional_apellido"],
                    //'foto'                   => $calificacion["profesional_foto"],
                    'conteo'                 => 1,
                    'sumatoria_puntualidad'  => $calificacion["puntualidad"],
                    'promedio_puntualidad'   => $calificacion["puntualidad"],
                    'sumatoria_servicio'     => $calificacion["servicio"],
                    'promedio_servicio'      => $calificacion["servicio"],
                    'sumatoria_presencia'    => $calificacion["presencia"],
                    'promedio_presencia'     => $calificacion["presencia"],
                    'sumatoria_conocimiento' => $calificacion["conocimiento"],
                    'promedio_conocimiento'  => $calificacion["conocimiento"],
                    'sumatoria_recomendado'  => $calificacion["recomendado"],
                    'promedio_recomendado'   => $calificacion["recomendado"],
                    'sumatoria_general'      => $calificacion["promedio"],
                    'promedio_general'       => $calificacion["promedio"]
                );
            } else {

                $profesionales_valorados[$calificacion["profesional_id"]]["conteo"] = $profesionales_valorados[$calificacion["profesional_id"]]["conteo"] + 1;

                // Puntualidad
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_puntualidad"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_puntualidad"] + $calificacion["puntualidad"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_puntualidad"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_puntualidad"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];

                // Servicio
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_servicio"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_servicio"] + $calificacion["servicio"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_servicio"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_servicio"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];

                // Presencia
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_presencia"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_presencia"] + $calificacion["presencia"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_presencia"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_presencia"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];

                // Conocimiento
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_conocimiento"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_conocimiento"] + $calificacion["conocimiento"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_conocimiento"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_conocimiento"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];

                // Recomendado
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_recomendado"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_recomendado"] + $calificacion["recomendado"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_recomendado"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_recomendado"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];

                // General
                $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_general"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_general"] + $calificacion["promedio"];
                $profesionales_valorados[$calificacion["profesional_id"]]["promedio_general"] = $profesionales_valorados[$calificacion["profesional_id"]]["sumatoria_general"] / $profesionales_valorados[$calificacion["profesional_id"]]["conteo"];
            }
        }

        //return $orderSort;
        $profesionales_valorados = $this->sortBySubArrayValue($profesionales_valorados, $orderField, $orderSort);

        if ($limit > 0) {
            $rest = count($profesionales_valorados) - $limit;
            array_splice($profesionales_valorados, count($profesionales_valorados) - $rest, $rest);
        }

        return $profesionales_valorados;
    }
}
