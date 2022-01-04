<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Calificacion;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;

/**
 * Class CalificacionController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class CalificacionController extends BaseAPIController
{
    // Calificacion URI's

    /**
     * Lista las calificaciones
     * @Rest\Get("/calificaciones", name="calificacion_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas los profesionales con valoraciones"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las calificaciones"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: nombre, puntualidad, servicio, presencia, conocimiento, ó recomendado.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     * @SWG\Parameter(name="findByServicio", in="path", type="integer", description="Filtrar por servicio prestado.")
     *
     * @SWG\Tag(name="Calificaciones")
     */
    public function getAllCalificacionesAction(Request $request)
    {

        try {

            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['nombre', 'puntualidad', 'servicio', 'presencia', 'conocimiento', 'recomendado'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Calificacion")->findCalificaciones($params);

            $calificados = array();

            foreach ($records as $r) {

                $foto = '';

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $r["foto"];
                $dir_uploads = $this->getParameter('dir_uploads') . $r["foto"];
                if ($r["foto"] && file_exists($dir_uploads)) {
                    $foto = $uploads;
                }

                $calificados[] = array(
                    'id'           => $r["id"],
                    'nombre'       => $r["nombre"],
                    'apellido'     => $r["apellido"],
                    'foto'         => $foto,
                    'puntualidad'  => number_format($r["promedio_puntualidad"], 2),
                    'servicio'     => number_format($r["promedio_servicio"], 2),
                    'presencia'    => number_format($r["promedio_presencia"], 2),
                    'conocimiento' => number_format($r["promedio_conocimiento"], 2),
                    'recomendado'  => number_format($r["promedio_recomendado"], 2),
                    'promedio'     => number_format($r["promedio_general"], 2),
                    'valoraciones' => $r["conteo"]
                );
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificados);
    }

    /**
     * obtener la información de una calificación dada el ID
     * @Rest\Get("/calificaciones/{id}", name="calificacion_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la calificación basado en parámetro ID.",
     *     @Model(type=Calificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la Calificacion"
     * )
     *
     * @SWG\Tag(name="Calificaciones")
     */
    public function getCalificacionAction(Request $request, $id)
    {

        try {

            $calificacion = $this->getDoctrine()->getManager()->getRepository("App:Calificacion")->find($id);
            if (!$calificacion || is_null($calificacion)) {
                return $this->JsonResponseNotFound();
            }

            $uploads_user = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $calificacion->getOrdenServicio()->getUser()->getFoto();
            $dir_uploads_user = $this->getParameter('dir_uploads') . $calificacion->getOrdenServicio()->getUser()->getFoto();
            if ($calificacion->getOrdenServicio()->getUser()->getFoto() && file_exists($dir_uploads_user)) {
                $calificacion->getOrdenServicio()->getUser()->setFoto($uploads_user);
            } else {
                $calificacion->getOrdenServicio()->getUser()->setFoto('');
            }

            /*$uploads_profesional = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $calificacion->getOrdenServicio()->getProfesional()->getUser()->getFoto();
            $dir_uploads_profesional = $this->getParameter('dir_uploads') . $calificacion->getOrdenServicio()->getProfesional()->getUser()->getFoto();
            if ($calificacion->getOrdenServicio()->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads_profesional)) {
                $calificacion->getOrdenServicio()->getProfesional()->getUser()->setFoto($uploads_profesional);
            } else {
                $calificacion->getOrdenServicio()->getProfesional()->getUser()->setFoto('');
            }*/
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificacion);
    }

    /**
     * crear una calificación
     * @Rest\Post("/calificaciones", name="calificacion_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Calificación agregada exitosamente",
     *     @Model(type=Calificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar la calificación"
     * )
     *
     * @SWG\Parameter(
     *     name="orden_servicio_id",
     *     in="body",
     *     type="integer",
     *     description="Id de la orden de servicio",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="puntualidad",
     *     in="body",
     *     type="integer",
     *     description="Valor de la puntualidad. Rango permitido: 1 al 5.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="servicio",
     *     in="body",
     *     type="integer",
     *     description="Valor del servicio. Rango permitido: 1 al 5.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="presencia",
     *     in="body",
     *     type="integer",
     *     description="Valor de la presencia. Rango permitido: 1 al 5.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="conocimiento",
     *     in="body",
     *     type="integer",
     *     description="Valor del conocimiento. Rango permitido: 1 al 5.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="recomendado",
     *     in="body",
     *     type="integer",
     *     description="Recomendado o no. Valores permitidos: 1=Sí, 0=No.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="comentarios",
     *     in="body",
     *     type="string",
     *     description="Comentarios",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Calificaciones")
     */
    public function addCalificacionAction(Request $request)
    {
        try {
            $orden_servicio_id = $request->request->get('orden_servicio_id', null);
            $puntualidad = $request->request->get("puntualidad", 1);
            $servicio = $request->request->get('servicio', 1);
            $presencia = $request->request->get('presencia', 1);
            $conocimiento = $request->request->get('conocimiento', 1);
            $recomendado = $request->request->get('recomendado', 0);
            $comentarios = $request->request->get('comentarios', null);

            if (is_null($orden_servicio_id)) {
                $response['value'] = $orden_servicio_id;
                $response['message'] = "Por favor introduzca la orden de servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $orden_servicio = $this->em->getRepository("App:OrdenServicio")->findOrdenServicioActivo($orden_servicio_id);
                if (!$orden_servicio) {
                    $response['value'] = $orden_servicio_id;
                    $response['message'] = "Orden de servicio no encontrada";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($orden_servicio->getEstatus() != 7) {
                        $response['value'] = $orden_servicio_id;
                        $response['message'] = "Orden de servicio debe estar en estado Finalizada para poder ser Calificada";
                        return $this->JsonResponseBadRequest($response);
                    }
                }
            }

            $qualification = $this->em->getRepository("App:Calificacion")->findByOS($orden_servicio_id);
            if ($qualification) {
                $response['value'] = $orden_servicio_id;
                $response['message'] = "Esta orden de servicio ya está calificada";
                return $this->JsonResponseBadRequest($response);
            }

            $puntualidad = intval($puntualidad);
            if (!($puntualidad == 1 || $puntualidad == 2 || $puntualidad == 3 || $puntualidad == 4 || $puntualidad == 5)) {
                $response['value'] = $puntualidad;
                $response['message'] = "Rango permitido de puntualidad: 1 al 5.";
                return $this->JsonResponseBadRequest($response);
            }

            $servicio = intval($servicio);
            if (!($servicio == 1 || $servicio == 2 || $servicio == 3 || $servicio == 4 || $servicio == 5)) {
                $response['value'] = $servicio;
                $response['message'] = "Rango permitido de servicio: 1 al 5.";
                return $this->JsonResponseBadRequest($response);
            }

            $presencia = intval($presencia);
            if (!($presencia == 1 || $presencia == 2 || $presencia == 3 || $presencia == 4 || $presencia == 5)) {
                $response['value'] = $presencia;
                $response['message'] = "Rango permitido de presencia: 1 al 5.";
                return $this->JsonResponseBadRequest($response);
            }

            $conocimiento = intval($conocimiento);
            if (!($conocimiento == 1 || $conocimiento == 2 || $conocimiento == 3 || $conocimiento == 4 || $conocimiento == 5)) {
                $response['value'] = $conocimiento;
                $response['message'] = "Rango permitido de conocimiento: 1 al 5.";
                return $this->JsonResponseBadRequest($response);
            }

            $recomendado = intval($recomendado);
            if (!($recomendado == 0 || $recomendado == 1)) {
                $response['value'] = $recomendado;
                $response['message'] = "Valores permitidos de recomendado: 1=Sí, 0=No..";
                return $this->JsonResponseBadRequest($response);
            }

            $calificacion = new Calificacion();
            $calificacion->setOrdenServicio($orden_servicio);
            $calificacion->setPuntualidad($puntualidad);
            $calificacion->setServicio($servicio);
            $calificacion->setPresencia($presencia);
            $calificacion->setConocimiento($conocimiento);
            $calificacion->setRecomendado($recomendado);
            $calificacion->setFechaCreacion(new \DateTime('now'));
            $calificacion->setComentarios($comentarios);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($calificacion);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $this->em->persist($calificacion);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificacion, 201);
    }

    /**
     * Lista las mejores calificaciones en un rango de fecha
     * @Rest\Get("/calificaciones/rango/{desde}/{hasta}", name="calificacion_list_all_rango")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene los prfesionales con las calificaciones mejor valoradas filtradas por rango de fecha"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo las calificaciones"
     * )
     *
     * @SWG\Parameter(name="desde", in="path", type="string", description="Fecha inicial en formato AAAA-MM-DD", required=true)
     * @SWG\Parameter(name="hasta", in="path", type="string", description="Fecha final en formato AAAA-MM-DD", required=true)
     *
     * @SWG\Tag(name="Calificaciones")
     */
    public function getAllCalificacionesRangoAction(Request $request, $desde, $hasta)
    {

        try {

            if (is_null($desde)) {
                $response['value'] = $desde;
                $response['message'] = "Por favor introduzca la fecha inicial";
                return $this->JsonResponseBadRequest($response);
            } else {
                $d = \DateTime::createFromFormat("Y-m-d", $desde);
                if (!($d && $d->format("Y-m-d") === $desde)) {
                    $response['value'] = $desde;
                    $response['message'] = "La fecha inicial debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $inicio = $desde . ' 00:00:00';
                }
            }

            if (is_null($hasta)) {
                $response['value'] = $hasta;
                $response['message'] = "Por favor introduzca la fecha final";
                return $this->JsonResponseBadRequest($response);
            } else {
                $d = \DateTime::createFromFormat("Y-m-d", $hasta);
                if (!($d && $d->format("Y-m-d") === $hasta)) {
                    $response['value'] = $hasta;
                    $response['message'] = "La fecha final debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $fin = $hasta . ' 23:59:59';
                }
            }

            if ($desde > $hasta) {
                $response['value'] = $desde;
                $response['message'] = "La fecha inicial debe ser menor a fecha final";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:Calificacion")->findCalificacionesRango($inicio, $fin);
            $calificados = array();

            foreach ($records as $r) {

                $foto = '';

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $r["foto"];
                $dir_uploads = $this->getParameter('dir_uploads') . $r["foto"];
                if ($r["foto"] && file_exists($dir_uploads)) {
                    $foto = $uploads;
                }

                $calificados[] = array(
                    'id'           => $r["id"],
                    'nombre'       => $r["nombre"],
                    'apellido'     => $r["apellido"],
                    'foto'         => $foto,
                    'puntualidad'  => number_format($r["promedio_puntualidad"], 2),
                    'servicio'     => number_format($r["promedio_servicio"], 2),
                    'presencia'    => number_format($r["promedio_presencia"], 2),
                    'conocimiento' => number_format($r["promedio_conocimiento"], 2),
                    'recomendado'  => number_format($r["promedio_recomendado"], 2),
                    'promedio'     => number_format($r["promedio_general"], 2),
                    'valoraciones' => $r["conteo"]
                );
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificados);
    }

    /**
     * obtener las calificaciones de un profesional
     * @Rest\Get("/calificaciones/profesional/{profesional_id}/{servicio_id}/{desde}/{hasta}", name="calificaciones_profesional")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene las calificaciones de un profesional",
     *     @Model(type=Calificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo las calificaciones del profesional"
     * )
     *
     * @SWG\Parameter(name="profesional_id", in="path", type="integer", description="ID del profesional")
     * @SWG\Parameter(name="servicio_id", in="path", type="integer", description="ID del servicio")
     * @SWG\Parameter(name="desde", in="path", type="string", description="Fecha inicial en formato AAAA-MM-DD")
     * @SWG\Parameter(name="hasta", in="path", type="string", description="Fecha final en formato AAAA-MM-DD")
     *
     * @SWG\Tag(name="Calificaciones")
     */
    public function getCalificacionesProfesionalAction(Request $request, $profesional_id, $servicio_id = 0, $desde = 0, $hasta = 0)
    {

        try {

            $profesional = $this->em->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);
            if (!$profesional) {
                $response['value'] = $profesional_id;
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            if ($servicio_id) {
                $servicio = $this->em->getRepository("App:Servicio")->findServicioActivo($servicio_id);
                if (!$servicio) {
                    $response['value'] = $servicio_id;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if ($desde) {
                $d = \DateTime::createFromFormat("Y-m-d", $desde);
                if (!($d && $d->format("Y-m-d") === $desde)) {
                    $response['value'] = $desde;
                    $response['message'] = "La fecha inicial debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $desde = $desde . ' 00:00:00';
                }
            }

            if ($hasta) {
                $d = \DateTime::createFromFormat("Y-m-d", $hasta);
                if (!($d && $d->format("Y-m-d") === $hasta)) {
                    $response['value'] = $hasta;
                    $response['message'] = "La fecha final debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $hasta = $hasta . ' 23:59:59';
                }
            }

            if ($desde && $hasta && $desde > $hasta) {
                $response['value'] = $desde;
                $response['message'] = "La fecha inicial debe ser menor a fecha final";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:Calificacion")->findCalificacionesProfesional($profesional_id, $servicio_id, $desde, $hasta);
            $calificaciones = array();

            foreach ($records as $r) {

                $foto = '';

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $r["profesional_foto"];
                $dir_uploads = $this->getParameter('dir_uploads') . $r["profesional_foto"];
                if ($r["profesional_foto"] && file_exists($dir_uploads)) {
                    $foto = $uploads;
                }

                $calificaciones[] = array(
                    'id'            => $r["id"],
                    'nombre'        => $r["profesional_nombre"],
                    'apellido'      => $r["profesional_apellido"],
                    'foto'          => $foto,
                    'puntualidad'   => $r["puntualidad"],
                    'servicio'      => $r["servicio"],
                    'presencia'     => $r["presencia"],
                    'conocimiento'  => $r["conocimiento"],
                    'recomendado'   => $r["recomendado"],
                    'promedio'      => number_format($r["promedio"], 2),
                    'fechaCreacion' => $r["fechaCreacion"],
                    'comentarios'   => is_null($r["comentarios"]) ? "" : $r["comentarios"]
                );
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificaciones);
    }
}
