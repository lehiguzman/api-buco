<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\OrdenServicioTarea;
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
 * Class OrdenServicioTareaController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class OrdenServicioTareaController extends BaseAPIController
{
    // OrdenServicioTarea URI's

    /**
     * obtener las tareas asociadas a una orden servicio ID
     * @Rest\Get("/ordenesServicio/tareas/{id}", name="ordenServicio_tarea_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las tareas asociadas a una orden de servicio basado en parámetro ID.",
     *     @Model(type=OrdenServicioTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las tareas de esta orden de servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la orden de servicio")
     *
     *
     * @SWG\Tag(name="Órdenes de Servicios | Tareas")
     */
    public function getTareasOrdenServicioAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioTarea")->findbyServiceOrder($id);

            foreach ($entities as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getTarea()->getServicio()->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getTarea()->getServicio()->getIcono();
                if ($entity->getTarea()->getServicio()->getIcono() && file_exists($dir_uploads)) {
                    $entity->getTarea()->getServicio()->setIcono($uploads);
                } else {
                    $entity->getTarea()->getServicio()->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * obtener las órdenes de servicios asociados a una tarea ID
     * @Rest\Get("/tareas/ordenesServicio/{id}", name="ordenesServicio_tarea_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos las órdenes de servicio asociadas a una tarea basado en parámetro ID.",
     *     @Model(type=OrdenServicioTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las órdenes de servicio de esta tarea"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la Tarea")
     *
     *
     * @SWG\Tag(name="Órdenes de Servicios | Tareas")
     */
    public function getOrdenesServicioTareaAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioTarea")->findbyTask($id);

            foreach ($entities as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getTarea()->getServicio()->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getTarea()->getServicio()->getIcono();
                if ($entity->getTarea()->getServicio()->getIcono() && file_exists($dir_uploads)) {
                    $entity->getTarea()->getServicio()->setIcono($uploads);
                } else {
                    $entity->getTarea()->getServicio()->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * agregar una nueva orden_servicio_tarea
     * @Rest\Post("/ordenesServicio/tareas", name="ordenServicio_tarea_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="OrdenServicioTarea agregada exitosamente",
     *     @Model(type=OrdenServicioTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar una nueva orden_servicio_tarea"
     * )
     *
     * @SWG\Parameter(
     *     name="orden_servicio_id",
     *     in="body",
     *     type="integer",
     *     description="ID de la orden de servicio",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Parameter(
     *     name="tarea_id",
     *     in="body",
     *     type="integer",
     *     description="ID de la tarea",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="cantidad",
     *     in="body",
     *     type="integer",
     *     description="Cantidad de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Órdenes de Servicios | Tareas")
     */
    public function addOrdenServicioTareaAction(Request $request)
    {
        $em = $this->em;
        try {

            $orden_servicio_id = $request->request->get('orden_servicio_id', null);
            $tarea_id = $request->request->get('tarea_id', null);
            $cantidad = $request->request->get('cantidad', null);

            //Consultar profesionales asociados y verificar que el usuario actual este asociado
            $profesionales = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findbyODS($orden_servicio_id);
            $profesionalAsociado = false;

            foreach($profesionales as $profesional) {
                if ($profesional->getProfesional()->getUser()->getId() == $this->getUser()->getId()) {                                    
                    $profesionalAsociado = true;
                }
            }

            if (is_null($orden_servicio_id)) {
                $response['value'] = $orden_servicio_id;
                $response['message'] = "Por favor introduzca la orden de servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $orden_servicio = $em->getRepository("App:OrdenServicio")->findOrdenServicioActivo($orden_servicio_id);
                if (!$orden_servicio) {
                    $response['value'] = $orden_servicio_id;
                    $response['message'] = "Orden de Servicio no encontrada";
                    return $this->JsonResponseBadRequest($response);
                } elseif (!in_array($orden_servicio->getEstatus(), [1, 2, 5])) {
                    $response['value'] = $orden_servicio_id;
                    $response['message'] = "Orden de Servicio debe estar en estado En espera (estado=1) o Confirmada (estado=2) para agregar tareas a la misma.";
                    return $this->JsonResponseBadRequest($response);
                } else {

                    // Validaciones de acuerdo al estatus de la OS
                    switch ($orden_servicio->getEstatus()) {
                        case 1: // En espera
                            if (!(in_array('ROLE_USER', $this->getUser()->getRoles()) || in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) || in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()))) {
                                return $this->JsonResponseSuccess(null, 403, "Usted no está sautorizado para agregar la tarea a esta orden de servicio.");
                            } elseif (in_array('ROLE_USER', $this->getUser()->getRoles()) && $orden_servicio->getUser()->getId() != $this->getUser()->getId()) {
                                return $this->JsonResponseSuccess(null, 403, "Usted no está autorizado para agregar la tarea a esta orden de servicio.");
                            }
                            break;
                        case 2: // Confirmada
                            if (!$this->isGranted('ROLE_PROFESIONAL')) {
                                return $this->JsonResponseSuccess(null, 403, "Usted no está autorizado para agregar la tarea a esta orden de servicio.");
                            } elseif (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) && !$profesionalAsociado) {
                                return $this->JsonResponseSuccess(null, 403, "Sólo un profesional asociado puede agregar la tarea a esta orden de servicio.");
                            }
                            break;
                        case 5: // Pendiente por aprobación del cliente
                            if (!$this->isGranted('ROLE_PROFESIONAL')) {
                                return $this->JsonResponseSuccess(null, 403, "Usted no está autorizado para agregar la tarea a esta orden de servicio.");
                            } elseif (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) && !$profesionalAsociado) {
                                return $this->JsonResponseSuccess(null, 403, "Sólo un profesional asociado puede agregar la tarea a esta orden de servicio.");
                            }
                            break;
                    }
                }
            }

            if (is_null($tarea_id)) {
                $response['value'] = $tarea_id;
                $response['message'] = "Por favor introduzca la tarea";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tarea = $em->getRepository("App:Tarea")->findTareaActiva($tarea_id);
                if (!$tarea) {
                    $response['value'] = $tarea_id;
                    $response['message'] = "Tarea no encontrada";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se verifica que la tarea corresponda con el servicio de la ODS
                    if ($tarea->getServicio()->getId() != $orden_servicio->getServicio()->getId()) {
                        $response['value'] = $tarea_id;
                        $response['message'] = "Tarea no corresponde con el servicio asociado a la ODS";
                        return $this->JsonResponseBadRequest($response);
                    }
                }
            }

            if (is_null($cantidad)) {
                $response['value'] = $cantidad;
                $response['message'] = "Por favor introduzca la cantidad";
                return $this->JsonResponseBadRequest($response);
            } 

            /*$orden_servicio_tarea = $em->getRepository('App:OrdenServicioTarea')->findOneBy(array(
                'ordenServicio' => $orden_servicio_id,
                'tarea' => $tarea_id
            ));

            if ($orden_servicio_tarea && $orden_servicio_tarea->getEstatus() != 3) {
                $response['value'] = $orden_servicio_tarea->getId();
                $response['message'] = "Ya existe una orden_servicio_tarea con orden_servicio_id=$orden_servicio_id y tarea_id=$tarea_id.";
                return $this->JsonResponseBadRequest($response);
            }*/

            // Monto de la tarea
            $monto = 0;

            $orden_servicio_profesional = $em->getRepository('App:OrdenServicioProfesional')->findbyODS($orden_servicio_id);

            foreach($orden_servicio_profesional as $odsProfesional) {

                $profesional_tarea = $em->getRepository('App:ProfesionalTarea')->findOneBy(array(
                    'profesional' => $odsProfesional->getProfesional()->getId(),
                    'tarea' => $tarea_id
                ));
                if ($profesional_tarea) {
                    $monto += $profesional_tarea->getPrecio();
                } else {
                    $response['value'] = $tarea_id;
                    $response['message'] = "Tarea no está asociada al profesional";
                    return $this->JsonResponseBadRequest($response);
                }
            }
            

            // Estatus para la orden_servicio_tarea
            // 1: Aprobada
            // 2: Agregada
            // 3: Eliminada

            //if ($orden_servicio_tarea) {
                // Solo se actualiza el estatus
            //    $entity = $orden_servicio_tarea;
            //} else {
                $entity = new OrdenServicioTarea();
                $entity->setOrdenServicio($orden_servicio);
                $entity->setCantidad($cantidad);
                $entity->setTarea($tarea);
                $entity->setMonto($monto);
            //}
            $entity->setEstatus(1);
            $em->persist($entity);
            $em->flush();

            if ($orden_servicio->getEstatus() == 2 || $orden_servicio->getEstatus() == 5) {

                // Si la orden de servicio está en estatus Confirmada, se pasa a Pendiente por aprobación
                $orden_servicio->setEstatus(5);
                $em->persist($orden_servicio);
                $em->flush();

                // Además el estatus de la orden_servicio_tarea pasa a Agregada
                $entity->setEstatus(2);
                $em->persist($entity);
                $em->flush();
            }

            // Calcular el monto de la OS y la comisión
            $calculo = $this->em->getRepository("App:OrdenServicioTarea")->calcularMontoOS($orden_servicio_id);

            // Actualizar en OS
            $orden_servicio->setMonto($calculo["monto"]);
            $orden_servicio->setComision($calculo["comision"]);
            $em->persist($orden_servicio);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * solicitar eliminar una orden_servicio_tarea dado el ID
     * @Rest\Delete("/ordenesServicio/tareas/{id}", name="ordenServicio_tarea_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La orden_servicio_tarea está solicitada a ser eliminada."
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la OrdenServicioTarea")
     *
     * @SWG\Tag(name="Órdenes de Servicios | Tareas")
     */
    public function deleteOrdenServicioTareaAction(Request $request, $id)
    {
        try {
            $eliminar = 0;

            $orden_servicio_tarea = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioTarea")->find($id);
            if (!$orden_servicio_tarea || is_null($orden_servicio_tarea)) {
                return $this->JsonResponseNotFound();
            }

            //Consultar profesionales asociados y verificar que el usuario actual este asociado
            $profesionales = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findbyODS($orden_servicio_tarea->getOrdenServicio()->getId());
            $profesionalAsociado = false;

            foreach($profesionales as $profesional) {
                if ($profesional->getProfesional()->getUser()->getId() == $this->getUser()->getId()) {                                    
                    $profesionalAsociado = true;
                }
            }

            if (!($orden_servicio_tarea->getOrdenServicio()->getEstatus() == 1 || $orden_servicio_tarea->getOrdenServicio()->getEstatus() == 2 || $orden_servicio_tarea->getOrdenServicio()->getEstatus() == 5)) {
                $response['value'] = $orden_servicio_tarea->getOrdenServicio()->getId();
                $response['message'] = "Orden de Servicio debe estar en estado En espera (estado=1) o Confirmada (estado=2) para eliminar tareas a la misma.";
                return $this->JsonResponseBadRequest($response);
            } else {

                // Validaciones de acuerdo al estatus de la OS
                switch ($orden_servicio_tarea->getOrdenServicio()->getEstatus()) {
                    case 1: // En espera
                        if (!(in_array('ROLE_USER', $this->getUser()->getRoles()) || in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()))) {
                            return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para eliminar la tarea a esta orden de servicio.");
                        } elseif (in_array('ROLE_USER', $this->getUser()->getRoles()) && $orden_servicio_tarea->getOrdenServicio()->getUser()->getId() != $this->getUser()->getId()) {
                            return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para eliminar la tarea a esta orden de servicio.");
                        } else {
                            $eliminar = 1;
                        }
                        break;
                    case 2: // Confirmada
                        if (!$this->isGranted('ROLE_PROFESIONAL')) {
                            return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para eliminar la tarea a esta orden de servicio.");
                        } elseif (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) && !$profesionalAsociado) {
                            return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede eliminar la tarea a esta orden de servicio.");
                        }
                        if ($orden_servicio_tarea->getEstatus() == 2) {
                            $eliminar = 1;
                        }
                        break;
                    case 5: // Pendiente por aprobación del cliente
                        if (!$this->isGranted('ROLE_PROFESIONAL')) {
                            return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para eliminar la tarea a esta orden de servicio.");
                        } elseif (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) && !$profesionalAsociado) {
                            return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede eliminar la tarea a esta orden de servicio.");
                        }
                        if ($orden_servicio_tarea->getEstatus() == 2) {
                            $eliminar = 1;
                        }
                        break;
                }
            }

            if ($eliminar) {
                $this->em->remove($orden_servicio_tarea);
            } else {
                $orden_servicio_tarea->setEstatus(3);
                $this->em->persist($orden_servicio_tarea);
            }
            $this->em->flush();

            if (!$eliminar && $orden_servicio_tarea->getOrdenServicio()->getEstatus() == 2) {

                $orden_servicio = $orden_servicio_tarea->getOrdenServicio();
                $orden_servicio->setEstatus(5);
                $this->em->persist($orden_servicio);
                $this->em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro solicitado para ser eliminado!");
    }

    /**
     * aprobar/rechazar el estatus de una orden_servicio_tarea dado el ID
     * @Rest\Put("/ordenesServicio/tareas/{id}", name="ordenServicio_tarea_status")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El estado de la orden_servicio_tarea fue actualizada exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la OrdenServicioTarea")
     *
     * @SWG\Parameter(
     *     name="rechazar",
     *     in="body",
     *     type="integer",
     *     description="0: Aprobar, 1: Rechazar",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Órdenes de Servicios | Tareas")
     */
    public function statusOrdenServicioTareaAction(Request $request, $id)
    {
        try {
            $rechazar = $request->request->get("rechazar", 0);

            $orden_servicio_tarea = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioTarea")->find($id);
            if (!$orden_servicio_tarea || is_null($orden_servicio_tarea)) {
                return $this->JsonResponseNotFound();
            }

            if (!(intval($rechazar) == 0 || intval($rechazar) == 1)) {
                $response['value'] = $rechazar;
                $response['message'] = "Valores permitidos del parámetro rechazar: 0: Aprobar, 1: Rechazar";
                return $this->JsonResponseBadRequest($response);
            }

            $accion = $rechazar ? 'rechazar' : 'aprobar';
            $accion_ejecutada = $rechazar ? 'rechazada' : 'aprobada';

            if (!($orden_servicio_tarea->getEstatus() == 2 || $orden_servicio_tarea->getEstatus() == 3)) {
                $response['value'] = $orden_servicio_tarea->getId();
                $response['message'] = "El estado de esta tarea debe estar en Agregada (2) o Eliminada (3).";
                return $this->JsonResponseBadRequest($response);
            }

            if (!(in_array('ROLE_USER', $this->getUser()->getRoles()) || in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()))) {
                return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para $accion la modificación sobre la orden de servicio.");
            } elseif (in_array('ROLE_USER', $this->getUser()->getRoles()) && $orden_servicio_tarea->getOrdenServicio()->getUser()->getId() != $this->getUser()->getId()) {
                return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para $accion la modificación sobre la orden de servicio.");
            }

            if ($rechazar) {
                if ($orden_servicio_tarea->getEstatus() == 2) {
                    $this->em->remove($orden_servicio_tarea);
                } else {
                    $orden_servicio_tarea->setEstatus(1);
                    $this->em->persist($orden_servicio_tarea);
                }
            } else {
                if ($orden_servicio_tarea->getEstatus() == 2) {
                    $orden_servicio_tarea->setEstatus(1);
                    $this->em->persist($orden_servicio_tarea);
                } else {
                    $this->em->remove($orden_servicio_tarea);
                }
            }
            $this->em->flush();

            // Se verifica si todas las tareas de la OS están aprobadas para pasar el estatus de la OS a Confirmada (2)
            $confirmar = 1;

            $osts = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioTarea")->findbyServiceOrder($orden_servicio_tarea->getOrdenServicio()->getId());
            foreach ($osts as $ost) {
                if ($ost->getEstatus() != 1) {
                    $confirmar = 0;
                    break;
                }
            }

            if ($confirmar) {
                $orden_servicio_tarea->getOrdenServicio()->setEstatus(2);
            }

            // Calcular el monto de la OS y la comisión
            $calculo = $this->em->getRepository("App:OrdenServicioTarea")->calcularMontoOS($orden_servicio_tarea->getOrdenServicio()->getId());

            // Actualizar en OS
            $orden_servicio_tarea->getOrdenServicio()->setMonto($calculo["monto"]);
            $orden_servicio_tarea->getOrdenServicio()->setComision($calculo["comision"]);
            $this->em->persist($orden_servicio_tarea->getOrdenServicio());
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($orden_servicio_tarea, 200, "¡Solicitud $accion_ejecutada con éxito!");
    }
}
