<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;

use App\Entity\OrdenServicioProfesional;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class OrdenServicioProfesionalController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class OrdenServicioProfesionalController extends BaseAPIController
{
    // OrdenServicioProfesional URI's

    /**
     * obtener los profesionales asociados a una orden servicio ID
     * @Rest\Get("/ordenServicioProfesionales/{id}", name="orden_servicio_profesional_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas los profesionales asociados a una orden de servicio basado en parámetro ID.",
     *     @Model(type=OrdenServicioProfesional::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas los profesionales de esta orden de servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la orden de servicio")
     *
     *
     * @SWG\Tag(name="Órdenes de Servicios | Profesionales")
     */
    public function getProfesionalesOrdenServicioAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository('App:OrdenServicioProfesional')->findbyODS($id);
            foreach ($entities as $entity) {
                if ($entity->getProfesional()->getServicio()) {
                    $icono = $entity->getProfesional()->getServicio()->getIcono();
                    if ($icono) {
                        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $icono;
                        $dir_uploads = $this->getParameter('dir_uploads') . $icono;
                        if (file_exists($dir_uploads)) {
                            $entity->getProfesional()->getServicio()->setIcono($uploads);
                        }
                    } else {
                        $entity->getProfesional()->getServicio()->setIcono('');
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * obtener las ordenes de servicio asociadas a un user ID
     * @Rest\Get("/ordenServicioProfesionales/user/{id}", name="orden_servicio_profesional_user_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las ordenes de servicio asociadas a una usuario basado en parámetro ID.",
     *     @Model(type=OrdenServicioProfesional::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas los ordenes de servicio de este usuario"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del usuario")
     *
     *
     * @SWG\Tag(name="Órdenes de Servicios | Profesionales")
     */
    public function getProfesionalesOrdenServicioUserAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository('App:OrdenServicioProfesional')->findByUser($id);
            foreach ($entities as $entity) {
                if ($entity->getProfesional()->getServicio()) {
                    $icono = $entity->getProfesional()->getServicio()->getIcono();
                    if ($icono) {
                        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $icono;
                        $dir_uploads = $this->getParameter('dir_uploads') . $icono;
                        if (file_exists($dir_uploads)) {
                            $entity->getProfesional()->getServicio()->setIcono($uploads);
                        }
                    } else {
                        $entity->getProfesional()->getServicio()->setIcono('');
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * obtener las órdenes de servicio asociadas a un Profesional ID
     * @Rest\Get("/ordenesServicio/profesionales/{id}", name="ordenServicio_profesional_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las órdenes de servicio asociadas a un profesional basado en parámetro ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las órdenes de servicio de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del profesional")     
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenesServicioProfesionalAction(Request $request, $id)
    {
        try {

            $user = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);           

            if (!$user) {
                $response['value'] = $user;
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $ordenesServicio = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findOrdenServicioProfesionalActivo($id);

            foreach ($ordenesServicio as $entity) {

                $uploads_user = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getOrdenServicio()->getUser()->getFoto();
                $dir_uploads_user = $this->getParameter('dir_uploads') . $entity->getOrdenServicio()->getUser()->getFoto();
                if ($entity->getOrdenServicio()->getUser()->getFoto() && file_exists($dir_uploads_user)) {
                    $entity->getOrdenServicio()->getUser()->setFoto($uploads_user);
                } else {
                    $entity->getOrdenServicio()->getUser()->setFoto('');
                }

                $uploads_profesional = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getProfesional()->getUser()->getFoto();
                $dir_uploads_profesional = $this->getParameter('dir_uploads') . $entity->getProfesional()->getUser()->getFoto();
                if ($entity->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads_profesional)) {
                    $entity->getProfesional()->getUser()->setFoto($uploads_profesional);
                } else {
                    $entity->getProfesional()->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ordenesServicio);
    }

    /**
     * obtener una órden de servicio asociada a un Profesional ID
     * @Rest\Get("/ordenServicio/profesional/{id}", name="ordenServicio_profesional_detail")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene órden de servicio asociada a un profesional basado en parámetro ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo la órden de servicio de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del profesional")
     *     
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenServicioProfesionalAction(Request $request, $id)
    {
        $em = $this->em;

        try {
            $ordenServicioProfesional = $em->getRepository('App:OrdenServicioProfesional')->findBy(array(
                'ordenServicio' => $id,
            ));
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ordenServicioProfesional, 201);
    }

    /**
     * estatus de profesional principal/backup/descartado de un profesional en la orden de servicio dado el ID
     * @Rest\Put("/ordenServicioProfesionales/{id}", name="orden_servicio_profesional_status")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El estado del profesional fue actualizado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la OrdenServicioProfesional")         
     * 
     * @SWG\Parameter(
     *     name="profesionales",
     *     in="body",
     *     type="array",
     *     description="Objeto con el id del profesional y su estatus correspondiente (1:confirmado, 2:Principal, 3:Backup, 4:Descartado)",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Órdenes de Servicios | Profesionales")
     */
    public function statusOrdenServicioProfesionalAction(Request $request, $id)
    {
        $em = $this->em;
        $actualiza_orden = false;
        $estatusOrden = 2;

        try {

            //$cantidadProfesionales = 0;
            $profesionales = $request->request->get('profesionales', null);

            foreach ($profesionales as $profesional) {

                $fechaHoraInicio = $profesional['fechaHoraInicio'];

                $orden_servicio_profesional = $em->getRepository('App:OrdenServicioProfesional')->findOneBy(array(
                    'ordenServicio' => $id,
                    'profesional' => $profesional['profesional_id'],
                ));

                if ($profesional['estatus'] == 2 || $profesional['estatus'] == 1) {
                    $actualiza_orden = true;
                }

                if (!is_null($fechaHoraInicio)) {
                    $actualiza_orden = true;

                    $d = \DateTime::createFromFormat("Y-m-d H:i", $fechaHoraInicio);

                    if (!($d && $d->format("Y-m-d H:i") === $fechaHoraInicio)) {
                        $response['value'] = $fechaHoraInicio;
                        $response['message'] = "La fecha y hora debe estar en formato AAAA-MM-DD HH:mm";
                        return $this->JsonResponseBadRequest($response);
                    } else {
                        $fechaHoraInicio = new \DateTime($fechaHoraInicio);
                    }

                    $orden_servicio_profesional->setFechaHoraInicio($fechaHoraInicio);
                    $estatusOrden = 4;
                }

                //Extraigo la cantidad de profesionales de la ODS
                $cantidadProfesionales = $orden_servicio_profesional->getOrdenServicio()->getCantidadProfesionales();
                $orden_servicio_profesional->setEstatus($profesional['estatus']);
                $em->persist($orden_servicio_profesional);
                $em->flush();
            }

            //Consulto profesionales de ODS con estatus = 2 (Principales) para actualizar los montos de las tareas
            $profesionales_estatus = $em->getRepository('App:OrdenServicioProfesional')->findBy(array(
                'ordenServicio' => $id,
                'estatus' => 2
            ));

            //Verificar profesionales que han confirmado
            /*$profesionales_confirmados = $em->getRepository('App:OrdenServicioProfesional')->findBy(array(
                'ordenServicio' => $id,                
                'estatus' => 1
            ));*/

            //Si la cantidad de profesionales confirmados es igual a la cantidad de profesionales de la orden se actualiza a estatus confirmada la orden
            /*if($cantidadProfesionales == count($profesionales_confirmados)) {
                $actualiza_orden = true;                
            }*/

            //Tareas a ser actualizadas
            $orden_servicio_tareas = $em->getRepository('App:OrdenServicioTarea')->findbyServiceOrder($id);

            foreach ($orden_servicio_tareas as $orden_servicio_tarea) {
                $monto = 0;
                foreach ($profesionales_estatus as $profesional) {
                    $profesional_tarea = $em->getRepository('App:ProfesionalTarea')->findOneBy(array(
                        'tarea' => $orden_servicio_tarea->getTarea()->getId(),
                        'profesional' => $profesional->getProfesional()->getId(),
                    ));
                    $monto = $monto + $profesional_tarea->getPrecio();
                }

                if ($monto > 0) {
                    $ODS_tarea = $em->getRepository("App:OrdenServicioTarea")->find($orden_servicio_tarea->getId());
                    $ODS_tarea->setMonto($monto);
                    $em->persist($ODS_tarea);
                    $em->flush();
                }
            }


            //Actualiza estatus de la ODS si consigue cualquier estatus de profesional con valor 2
            if ($actualiza_orden) {
                $ODS = $em->getRepository("App:OrdenServicio")->findOrdenServicioActivo($id);

                $calculo = $em->getRepository("App:OrdenServicioTarea")->calcularMontoOS($id);

                $ODS->setMonto($calculo["monto"]);
                $ODS->setComision($calculo["comision"]);
                $ODS->setEstatus($estatusOrden);
                $em->persist($ODS);
                $em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($orden_servicio_profesional, 201);
    }

    /**
     * Consultar si el profesional tiene ordenes confirmadas en un rango de horas de una fecha determinada
     * @Rest\Get("/ordenServicioProfesionales/rangoFecha/{id}", name="orden_servicio_profesional_rangofecha")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene órden de servicio asociada a un profesional por rengo de hora en determinada fecha basado en parámetro ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo la órden de servicio de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del profesional")
     * @SWG\Parameter(name="fechaDesde", in="path", type="string", description="Fecha desde")
     * @SWG\Parameter(name="fechaHasta", in="path", type="string", description="Fecha hasta")
     *     
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios ")
     */
    public function getODSRangoFechaAction(Request $request, $id)
    {
        $params = $request->query->all();
        $fechaDesde = isset($params['fechaDesde']) ? $params['fechaDesde'] : null;
        $fechaHasta = isset($params['fechaHasta']) ? $params['fechaHasta'] : null;

        try {
            //Obtiene las ODS por rango de fecha y hora
            $ODS = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findDisponibilidad($id, $fechaDesde, $fechaHasta);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ODS, 201);
    }
}
