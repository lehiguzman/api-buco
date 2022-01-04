<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ODSFotos;
use App\Entity\ODSRechazadas;
use App\Entity\OrdenServicio;
use App\Entity\OrdenServicioProfesional;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// Servicio PayUs
use App\Service\PayUsServicio;

/**
 * Class OrdenServicioController
 *
 * @Route("/api/v1/ordenesServicio")
 * @IsGranted("ROLE_USER")
 */
class OrdenServicioController extends BaseAPIController
{
    /**
     * obtener las órdenes de servicio asociadas a un cliente ID
     * @Rest\Get("/usuarios/{id}", name="ordenServicio_user_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las órdenes de servicio asociadas a un cliente basado en parámetro ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las órdenes de servicio de este cliente"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del usuario")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenesServicioUserAction(Request $request, $id)
    {
        try {
            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($id);

            if (!$user) {
                $response['value'] = $user;
                $response['message'] = "Usuario no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $ordenesServicio = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findOrdenServicioClienteActivo($id);

            foreach ($ordenesServicio as $entity) {

                $uploads_user = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getUser()->getFoto();
                $dir_uploads_user = $this->getParameter('dir_uploads') . $entity->getUser()->getFoto();
                if ($entity->getUser()->getFoto() && file_exists($dir_uploads_user)) {
                    $entity->getUser()->setFoto($uploads_user);
                } else {
                    $entity->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ordenesServicio);
    }

    /**
     * agregar una nueva orden de servicio
     * @Rest\Post("", name="ordenServicio_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Orden de servicio agregada exitosamente",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar una nueva orden de servicio"
     * )
     *
     * @SWG\Parameter(
     *     name="user_id",
     *     in="body",
     *     type="integer",
     *     description="Id del cliente de la orden de servicio",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="servicio_id",
     *     in="body",
     *     type="integer",
     *     description="Id del servicio de la orden de servicio",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="profesionales",
     *     in="body",
     *     type="string",
     *     description="Arreglo de profesionales asociados a la orden.",
     *     schema={},
     *     required=false
     * ) 
     *
     * @SWG\Parameter(
     *     name="fechaHora",
     *     in="body",
     *     type="datetime",
     *     description="Fecha y hora de la orden de servicio en formato AAAA-MM-DD HH:mm",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="metodoPago_id",
     *     in="body",
     *     type="integer",
     *     description="Id del método de pago de la orden de servicio",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="clienteTarjeta",
     *     in="body",
     *     type="integer",
     *     description="Id de la tarjeta del cliente",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="monto",
     *     in="body",
     *     type="float",
     *     description="Monto de la ODS",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="porcentajeBackup",
     *     in="body",
     *     type="float",
     *     description="Porcentaje del pago de profesional backup de la ODS de Talento",
     *     schema={},
     *     required=true
     * ) 
     * 
     * @SWG\Parameter(
     *     name="montoEfectivo",
     *     in="body",
     *     type="float",
     *     description="Monto Efectivo para vuelto de la ODS",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="float",
     *     description="Latitud",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="float",
     *     description="Longitud",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="direccion",
     *     in="body",
     *     type="string",
     *     description="Dirección de la orden de servicio",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="file",
     *     in="body",
     *     type="string",
     *     description="Un arreglo de archivos de fotos. n representa el índice del arreglo.",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="titulo",
     *     in="body",
     *     type="string",
     *     description="Titulo de la orden de servicio de Talento",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción de la orden de servicio",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="observacion",
     *     in="body",
     *     type="string",
     *     description="Observación de la orden de servicio",
     *     schema={},
     *     required=false
     * ) 
     * 
     * @SWG\Parameter(
     *     name="cantidadProfesionales",
     *     in="body",
     *     type="string",
     *     description="Cantidad de profesionales de la orden de servicio",
     *     schema={},
     *     required=false
     * ) 
     * 
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function addOrdenServicioAction(Request $request)
    {
        $em = $this->em;
        try {
            $user_id = $request->request->get('user_id', null);
            $servicio_id = $request->request->get('servicio_id', null);
            $metodoPago_id = $request->request->get('metodoPago_id', null);
            $clienteTarjeta = $request->request->get('clienteTarjeta', null);
            $profesionales = $request->request->get('profesionales', null);
            $fechaHora = $request->request->get('fechaHora', null);
            $monto = $request->request->get('monto', 0);
            $porcentajeBackup = $request->request->get('porcentajeBackup', 0);
            $montoEfectivo = $request->request->get('montoEfectivo', null);
            $latitud = $request->request->get('latitud', null);
            $longitud = $request->request->get('longitud', null);
            $direccion = $request->request->get('direccion', null);
            $titulo = $request->request->get('titulo', null);
            $descripcion = $request->request->get('descripcion', null);
            $observacion = $request->request->get('observacion', null);
            $cantidadProfesionales = $request->request->get('cantidadProfesionales', 0);
            $fotos = $request->request->get('file');

            if (is_null($user_id)) {
                $response['value'] = $user_id;
                $response['message'] = "Por favor introduzca el usuario";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $em->getRepository("App:User")->findUserActivo($user_id);
                if (!$user) {
                    $response['value'] = $user_id;
                    $response['message'] = "Usuario no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($servicio_id)) {
                $response['value'] = $servicio_id;
                $response['message'] = "Por favor introduzca el servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $servicio = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServicioActivo($servicio_id);
                if (!$servicio) {
                    $response['value'] = $servicio_id;
                    $response['message'] = "Usuario no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($fechaHora)) {
                $response['value'] = $fechaHora;
                $response['message'] = "Por favor introduzca fecha y hora";
                return $this->JsonResponseBadRequest($response);
            } else {
                $d = \DateTime::createFromFormat("Y-m-d H:i", $fechaHora);
                if (!($d && $d->format("Y-m-d H:i") === $fechaHora)) {
                    $response['value'] = $fechaHora;
                    $response['message'] = "La fecha y hora debe estar en formato AAAA-MM-DD HH:mm";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $fechaHora = new \DateTime($fechaHora);
                }
            }

            if (is_null($metodoPago_id)) {
                $response['value'] = $metodoPago_id;
                $response['message'] = "Por favor introduzca el método de pago";
                return $this->JsonResponseBadRequest($response);
            }

            if (intval($metodoPago_id) === 1 && is_null($clienteTarjeta)) {
                $response['value'] = $metodoPago_id;
                $response['message'] = "Por favor introduzca la TDC del cliente";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($monto)) {
                $response['value'] = $monto;
                $response['message'] = "Por favor introduzca el monto";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_numeric($monto)) {
                $response['value'] = $monto;
                $response['message'] = "El monto debe ser numérico";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($monto < 0) {
                    $response['value'] = $monto;
                    $response['message'] = "El monto debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($latitud)) {
                $response['value'] = $latitud;
                $response['message'] = "Por favor introduzca la latitud";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_numeric($latitud)) {
                $response['value'] = $latitud;
                $response['message'] = "La latitud debe ser numérica";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($longitud)) {
                $response['value'] = $longitud;
                $response['message'] = "Por favor introduzca la longitud";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_numeric($longitud)) {
                $response['value'] = $longitud;
                $response['message'] = "La longitud debe ser numérica";
                return $this->JsonResponseBadRequest($response);
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            // Cargar Entidades
            $MetodoPago = $em->getRepository("App:MetodoPago")->findOneBy([
                'id' => $metodoPago_id,
                'status' => 1,
                'eliminado' => 0
            ]);
            if (!$MetodoPago) {
                $response['value'] = $metodoPago_id;
                $response['message'] = "Método de Pago no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            // Método Pago TDC y en linea
            if ($MetodoPago->getId() == 1 && $MetodoPago->getPagoLinea()) {
                $ClienteTarjeta = $em->getRepository("App:ClienteTarjetas")->findOneBy([
                    'id' => $clienteTarjeta,
                    'cliente' => $this->getUser()->getId(),
                    'eliminado' => 0
                ]);
                if (!$ClienteTarjeta) {
                    $response['value'] = $clienteTarjeta;
                    $response['message'] = "La tarjeta configurada no fue encontrada";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $ODS = new OrdenServicio();
            $ODS->setUser($user);
            $ODS->setServicio($servicio);
            $ODS->setMetodoPago($MetodoPago);
            if ($MetodoPago->getId() == 1 && $MetodoPago->getPagoLinea()) {
                $ODS->setClienteTarjeta($ClienteTarjeta);
            }
            $ODS->setFechaHora($fechaHora);
            $ODS->setLatitud(floatval($latitud));
            $ODS->setLongitud(floatval($longitud));
            $ODS->setDireccion($direccion);
            $ODS->setTitulo($titulo);
            $ODS->setDescripcion($descripcion);
            $ODS->setObservacion($observacion);
            $ODS->setMonto($monto); // El monto se calcula cuando se agreguen las tareas a la OS
            $ODS->setPorcentajeBackup($porcentajeBackup);
            $ODS->setMontoEfectivo($montoEfectivo);
            $ODS->setComision(0); // La comisión se calcula cuando se agreguen las tareas a la OS
            $ODS->setCantidadProfesionales($cantidadProfesionales);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($ODS);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($ODS);
            $em->flush();

            if (isset($profesionales) && is_array($profesionales)) {
                foreach ($profesionales as $profesional_id) {
                    $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);

                    $ordenServicioProfesional = new OrdenServicioProfesional();
                    $ordenServicioProfesional->setOrdenServicio($ODS);
                    $ordenServicioProfesional->setProfesional($profesional);
                    $ordenServicioProfesional->setEstatus(0);

                    $this->em->persist($ordenServicioProfesional);
                    $this->em->flush();
                }
            } else {
                $response['value'] = $profesionales;
                $response['message'] = "Por favor introduzca al menos un profesional";
                return $this->JsonResponseBadRequest($response);
            }

            if (isset($fotos) && is_array($fotos)) {
                // Guardar las fotos
                $dir_uploads = $this->getParameter('dir_uploads');
                $dir = $dir_uploads . 'ods/' . $ODS->getId() . '/';
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir, 750, true);
                }

                $msg = "";
                if (!file_exists($dir_uploads) && !is_dir($dir_uploads)) {
                    $msg .= " Sin embargo, las fotos nos se almacenaron. Asegúrese de que exista el siguiente directorio con permisos de lectura y escritura: " . $dir_uploads;
                } else {

                    foreach ($fotos as $foto) {

                        if (!is_null($foto)) {

                            $ext = 'jpg';

                            $nuevo_nombre = $this->generateRandomString() . '.' . $ext;

                            $ifp = fopen($dir . $nuevo_nombre, 'wb');
                            $data = explode(',', $foto);
                            if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                                fwrite($ifp, base64_decode($data[1]));
                                fclose($ifp);

                                // Se crea el registro en ODSFotos
                                $ODSFotos = new ODSFotos();
                                $ODSFotos->setOrdenServicio($ODS);
                                $ODSFotos->setNombre($nuevo_nombre);
                                $ODSFotos->setRuta('ods/' . $ODS->getId() . '/' . $nuevo_nombre);
                                $this->em->persist($ODSFotos);
                                $this->em->flush();
                            }
                        }
                    }
                }
            }

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {
            $ordenID = $ODS->getId();
            //Consultar profesionales asociados y verificar que el usuario actual este asociado
            $profesionales = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findbyODS($ordenID);
            $notificacion = [
                "¡Orden de Servicio #$ordenID creada por el Cliente!",
                "Ve al detalle de la Orden para poder confirmar o rechazar la misma."
            ];
            $data = $this->em->getRepository("App:OrdenServicio")->findODSArray($ODS->getId())[0];
            $data['tipo'] = "ods";

            foreach ($profesionales as $profesional) {
                $usuarioTokens = $this->gestionTokens->usuarioPushTokens($profesional->getProfesional()->getUser()->getId());
                if (count($usuarioTokens) > 0) {
                    foreach ($usuarioTokens as $value) {
                        $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                    }
                }
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE NOTIFICACIONES */

        return $this->JsonResponseSuccess($ODS, 201);
    }

    /**
     * obtener orden de servicio asociada a un ID
     * @Rest\Get("/{id}", name="ordenServicio_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene una orden de servicio asociadas a parametro ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo orden de servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del usuario")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenServicioAction(Request $request, $id)
    {
        try {
            $ODS = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findOrdenServicioActivo($id);
            $uploads_user = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $ODS->getUser()->getFoto();
            $dir_uploads_user = $this->getParameter('dir_uploads') . $ODS->getUser()->getFoto();
            if ($ODS->getUser()->getFoto() && file_exists($dir_uploads_user)) {
                $ODS->getUser()->setFoto($uploads_user);
            } else {
                $ODS->getUser()->setFoto('');
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ODS);
    }

    /**
     * cambiar el estatus de una orden de servicio
     * @Rest\Put("/{id}", name="ordenServicio_estatus")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El estatus de la orden de servicio fue actualizado satisfactoriamente",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar el estatus de la orden de servicio."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID de la orden de servicio"
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus de la orden de servicio. Valores permitidos: 0: Cancelada, 2: Confirmada, 3: Rechazada, 7: Pagada/Finalizada, 8: Calificada.",
     *     schema={},
     *     required=true
     * )
     * 
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function statusOrdenServicioAction(Request $request, $id, PayUsServicio $payusServ)
    {
        try {
            $ODS = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findOrdenServicioActivo($id);
            if (!$ODS || is_null($ODS)) {
                return $this->JsonResponseNotFound();
            }

            $estatus = $request->request->get('estatus', null);
            $estatus_anterior = 1;
            $modificar = false;

            //Consultar profesionales asociados y verificar que el usuario actual este asociado
            $profesionales = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findbyODS($id);
            $profesionalAsociado = false;

            foreach ($profesionales as $profesional) {
                if ($profesional->getProfesional()->getUser()->getId() == $this->getUser()->getId()) {
                    $profesionalAsociado = true;
                }
            }

            if (is_null($estatus)) {
                $response['value'] = $estatus;
                $response['message'] = "Por favor introduzca el estatus";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!in_array(intval($estatus), [0, 2, 3, 4, 7, 8])) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos: 0: Cancelada, 2: Confirmada, 3: Rechazada, 4: Iniciada, 7: Pagada/Finalizada, 8: Calificada.";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($ODS->getEstatus() != intval($estatus)) {
                        $estatus_anterior = $ODS->getEstatus();
                        $ODS->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            // Validaciones de acuerdo al estatus nuevo
            switch ($ODS->getEstatus()) {
                case 0: // cancelada
                    if (!(in_array('ROLE_USER', $this->getUser()->getRoles()) || in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()))) {
                        return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para cancelar esta orden de servicio.");
                    } elseif (in_array('ROLE_USER', $this->getUser()->getRoles()) && $ODS->getUser()->getId() != $this->getUser()->getId()) {
                        return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para cancelar esta orden de servicio.");
                    } elseif (!($estatus_anterior == 1 || $estatus_anterior == 2)) {
                        $response['value'] = $id;
                        $response['message'] = "Esta orden de servicio tiene un estado no válido para ser cancelada.";
                        return $this->JsonResponseBadRequest($response);
                    }
                    break;
                case 2: // confirmada
                    if (!(in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) || in_array('ROLE_USER', $this->getUser()->getRoles()))) {
                        return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para confirmar esta orden de servicio.");
                    } else {
                        if (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles())) {
                            if (!$profesionalAsociado) {
                                return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede confirmar esta orden de servicio.");
                            }
                            if ($estatus_anterior != 1) {
                                $response['value'] = $id;
                                $response['message'] = "Esta orden de servicio tiene un estado no válido para ser confirmada.";
                                return $this->JsonResponseBadRequest($response);
                            }
                        } elseif (in_array('ROLE_USER', $this->getUser()->getRoles())) {
                            if ($ODS->getUser()->getId() != $this->getUser()->getId()) {
                                return $this->JsonResponseSuccess($id, 403, "Sólo el cliente puede aprobar la modificación de esta orden de servicio.");
                            } elseif ($estatus_anterior != 5) {
                                $response['value'] = $id;
                                $response['message'] = "Esta orden de servicio tiene un estado no válido para ser confirmada.";
                                return $this->JsonResponseBadRequest($response);
                            }
                        } elseif ($estatus_anterior != 1) {
                            $response['value'] = $id;
                            $response['message'] = "Esta orden de servicio tiene un estado no válido para ser confirmada.";
                            return $this->JsonResponseBadRequest($response);
                        }
                    }
                    break;
                case 3: // rechazada
                    if (!(in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()))) {
                        if (!$profesionalAsociado) {
                            return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede confirmar esta orden de servicio.");
                        }
                    } elseif ($estatus_anterior != 1) {
                        $response['value'] = $id;
                        $response['message'] = "Esta orden de servicio tiene un estado no válido para ser rechazada.";
                        return $this->JsonResponseBadRequest($response);
                    }
                    break;
                case 4: // Iniciada
                    if (!(in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()))) {
                        if (!$profesionalAsociado) {
                            return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede iniciar esta orden de servicio.");
                        }
                    } elseif ($estatus_anterior != 2) {
                        $response['value'] = $id;
                        $response['message'] = "Esta orden de servicio tiene un estado no válido para ser rechazada.";
                        return $this->JsonResponseBadRequest($response);
                    }
                    break;
                case 7: // finalizada
                    if (!$this->isGranted('ROLE_PROFESIONAL')) {
                        return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para finalizar esta orden de servicio.");
                    } elseif (in_array('ROLE_PROFESIONAL', $this->getUser()->getRoles()) && !$profesionalAsociado) {
                        return $this->JsonResponseSuccess($id, 403, "Sólo un profesional asociado puede finalizar esta orden de servicio.");
                    } elseif ($estatus_anterior != 4) {
                        $response['value'] = $id;
                        $response['message'] = "Esta orden de servicio tiene un estado no válido para ser finalizada.";
                        return $this->JsonResponseBadRequest($response);
                    }
                    break;
                case 8: // calificada
                    if (!(in_array('ROLE_USER', $this->getUser()->getRoles()) && $ODS->getUser()->getId() == $this->getUser()->getId())) {
                        return $this->JsonResponseSuccess($id, 403, "Usted no está autorizado para calificar esta orden de servicio.");
                    } elseif ($estatus_anterior != 7) {
                        $response['value'] = $id;
                        $response['message'] = "Esta orden de servicio tiene un estado no válido para ser calificada.";
                        return $this->JsonResponseBadRequest($response);
                    }
                    break;
            }

            // Lógica para uso de la Pasarela de Pago
            if ($ODS->getClienteTarjeta() && $ODS->getMonto()) {
                $tokenPayus = $ODS->getClienteTarjeta()->getTokenPayus();
                // >>> Servicio Pasarela de Pago PayUs
                $datos = [
                    'orden' => $ODS->getID(),
                    'montoTotal' => $ODS->getMonto(),
                    'cardToken' => $tokenPayus,
                ];
                $result = $payusServ->procesarPago($datos);
                // <<< Servicio Pasarela de Pago PayUs
                $modificar = true;
                if ($result['confirmed']) {
                    $ODS->setEstatus(7); // Pagado
                } else {
                    $ODS->setEstatus(6); // Pago Pendiente
                }
            }

            if ($modificar) {
                $ODS->setUpdatedAt(new \DateTime('now'));

                // ODS Cancelada por Cliente
                if ($ODS->getEstatus() == 0) {
                    $PorcentajePenalizacion = $ODS->getServicio()->getPorcentajePenalizacion();
                    if ($PorcentajePenalizacion) {
                        $montoPenalizado = ($ODS->getMonto() * $PorcentajePenalizacion) / 100;
                        // establecer monto de penalizacion
                        $ODS->setMontoPenalizacion($montoPenalizado);
                    }
                }

                // si el estatus es Rechazada se suma a las ordenes rechazadas del profesional
                if ($ODS->getEstatus() == 3) {
                    foreach ($profesionales as $profesional) {
                        if ($profesional->getProfesional()->getUser()->getId() == $this->getUser()->getId()) {
                            // Registras ODS Rechazada
                            $ODSRechazada = new ODSRechazadas();
                            $ODSRechazada->setProfesional($profesional->getProfesional());
                            $ODSRechazada->setOrdenServicio($ODS);
                            $this->em->persist($ODSRechazada);

                            $ordenesRechazadas = $profesional->getProfesional()->getOrdenesRechazadas() + 1;
                            $profesional->getProfesional()->setOrdenesRechazadas(intval($ordenesRechazadas));
                            $this->em->persist($profesional);

                            $this->em->flush();
                        }
                    }
                }

                // si el estatus es Finalizada se calcula la comisión y se actualiza el estado de los profesionales a (1:disponible)
                if ($ODS->getEstatus() == 7) {
                    foreach ($profesionales as $profesional) {
                        if ($profesional->getProfesional()->getEstatus() != 4) {
                            $profesional->getProfesional()->setEstatus(1);
                            $this->em->persist($profesional);
                            $this->em->flush();
                        }
                    }
                    $ODS = $this->calcularBuconexion->costoBuconexion($ODS);
                }

                $this->em->persist($ODS);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 500, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {

            $ordenID = $ODS->getId();
            $enviarCliente = false;
            $enviarProfesional = false;
            $tipoSistema = $ODS->getServicio()->getSistemaTipo();

            $notificacion = [
                "¡Orden de Servicio #$ordenID aztualizó su estatus!",
                "Ve al detalle de la Orden para más información."
            ];
            if ($tipoSistema == 1) {
                $AfiPro = "Afiliado";
            } elseif ($tipoSistema == 2) {
                $AfiPro = "Profesional";
            }

            switch ($ODS->getEstatus()) {
                case 0: // cancelada
                    $enviarProfesional = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID cancelada por el Cliente!",
                        "Ve al detalle de la Orden para más información."
                    ];
                    break;
                case 2: // confirmada
                    $enviarCliente = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID confirmada por el $AfiPro!",
                        "Ve al detalle de la Orden para más información."
                    ];
                    break;
                case 3: // rechazada
                    $enviarCliente = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID rechazada por el $AfiPro!",
                        "Ve al detalle de la Orden para más información."
                    ];
                    break;
                case 4: // iniciada
                    $enviarCliente = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID iniciada por el $AfiPro!",
                        "Ve al detalle de la Orden para más información."
                    ];
                    break;
                case 7: // finalizada
                    $enviarCliente = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID finalizada por el $AfiPro!",
                        "Ya puedes realizar la Calificación de la misma."
                    ];
                    break;
                case 8: // calificada
                    $enviarProfesional = true;
                    $notificacion = [
                        "¡Orden de Servicio #$ordenID calificada por el Cliente!",
                        "Ve al detalle de la Orden para ver su valoración."
                    ];
                    break;
            }

            // datos de la Orden de Servicio
            $data = $this->em->getRepository("App:OrdenServicio")->findODSArray($ODS->getId())[0];
            $data['tipo'] = "ods";

            foreach ($profesionales as $profesional) {

                $profesionalTokens = $this->gestionTokens->usuarioPushTokens($profesional->getProfesional()->getUser()->getId());

                // notificaciones al afiliado/profesional
                if ($enviarProfesional && count($profesionalTokens) > 0) {
                    foreach ($profesionalTokens as $value) {
                        $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                    }
                }
            }

            $clienteTokens = $this->gestionTokens->usuarioPushTokens($ODS->getUser()->getId());

            // notificaciones al cliente
            if ($enviarCliente && count($clienteTokens) > 0) {
                foreach ($clienteTokens as $value) {
                    $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                }
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE NOTIFICACIONES */

        return $this->JsonResponseSuccess($ODS, 200);
    }

    /**
     * cambiar el metodo de pago de una orden de servicio
     * @Rest\Put("/metodoPago/{id}", name="ordenServicio_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El metodo de pago de la orden de servicio fue actualizado satisfactoriamente",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar el metodo de pago de la orden de servicio."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID de la orden de servicio"
     * )
     *
     * @SWG\Parameter(
     *     name="metodoPago_id",
     *     in="body",
     *     type="integer",
     *     description="metodo de pago",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="metodoPagoCliente_id",
     *     in="body",
     *     type="integer",
     *     description="metodo de pago del cliente",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="montoEfectivo",
     *     in="body",
     *     type="float",
     *     description="Monto Efectivo para vuelto de la ODS",
     *     schema={},
     *     required=true
     * )
     * 
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function metodoPagoOrdenServicioAction(Request $request, $id)
    {
        try {
            $ODS = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findOrdenServicioActivo($id);
            if (!$ODS || is_null($ODS)) {
                return $this->JsonResponseNotFound();
            }

            //Consultar profesionales asociados y verificar que el usuario actual este asociado
            $profesionales = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findbyODS($id);

            $metodoPago_id = $request->request->get('metodoPago_id', null);
            $metodoPagoCliente_id = $request->request->get('metodoPagoCliente_id', null);
            $montoEfectivo = $request->request->get('montoEfectivo', null);
            $modificar = false;

            if (!is_null($metodoPago_id) && $ODS->getMetodoPago()->getId() != $metodoPago_id) {
                $MetodoPago = $this->em->getRepository("App:MetodoPago")->findMetodoPagoActivo($metodoPago_id);
                if (!$MetodoPago) {
                    $response['value'] = $metodoPago_id;
                    $response['message'] = "Método de pago no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {

                    if (!$MetodoPago->getPagoLinea()) {
                        $ODS->setMetodoPagoCliente(null);
                    }

                    $ODS->setMetodoPago($MetodoPago);
                    $modificar = true;
                }
            }

            if (!is_null($metodoPagoCliente_id)) {
                $metodoPagoCliente = $this->em->getRepository("App:MetodoPagoCliente")->findMetodoPagoActivo($metodoPagoCliente_id);
                if (!$metodoPagoCliente) {
                    $response['value'] = $metodoPagoCliente_id;
                    $response['message'] = "Método de pago del cliente no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $ODS->setMetodoPagoCliente($metodoPagoCliente);
                    $modificar = true;
                }
            }

            if ($ODS->getMontoEfectivo() != $montoEfectivo) {
                $ODS->setMontoEfectivo($montoEfectivo);
                $modificar = true;
            }

            if ($modificar) {
                $ODS->setUpdatedAt(new \DateTime('now'));
                $this->em->persist($ODS);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {
            $ordenID = $ODS->getId();
            $notificacion = [
                "¡Orden de Servicio #$ordenID cambio del método de pago por el Cliente!",
                "Ve al detalle de la Orden para más información."
            ];
            $data = $this->em->getRepository("App:OrdenServicio")->findODSArray($ODS->getId())[0];
            $data['tipo'] = "ods";

            foreach ($profesionales as $profesional) {
                $usuarioTokens = $this->gestionTokens->usuarioPushTokens($ODS->getProfesional()->getUser()->getId());

                if (count($usuarioTokens) > 0) {
                    foreach ($usuarioTokens as $value) {
                        $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                    }
                }
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE NOTIFICACIONES */

        return $this->JsonResponseSuccess($ODS, 200);
    }

    /**
     * Lista las órdenes de servicio
     * @Rest\Get("", name="ordenServicio_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las órdenes",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las órdenes de servicio"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o estatus.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: cliente.")
     * @SWG\Parameter(name="searchProfesional", in="path", type="string", description="Valor a buscar en campos: profesional.")
     * @SWG\Parameter(name="findByEstatus", in="path", type="integer", description="Filtrar por estatus.")
     * @SWG\Parameter(name="findByFechas", in="path", type="integer", description="Filtrar por un rango. Se debe agregar fechas desde y hasta.")
     * @SWG\Parameter(name="desde", in="path", type="string", description="Valor de la fecha inicial en el rango de fechas en formato AAAA-MM-DD. Válido si findByFechas=1.")
     * @SWG\Parameter(name="hasta", in="path", type="string", description="Valor de la fecha final en el rango de fechas en formato AAAA-MM-DD. Válido si findByFechas=1.")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenesServicioAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'estatus'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $ordenesServicio = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findOrdenesServicio($params);

            foreach ($ordenesServicio as $entity) {

                $uploads_user = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getUser()->getFoto();
                $dir_uploads_user = $this->getParameter('dir_uploads') . $entity->getUser()->getFoto();
                if ($entity->getUser()->getFoto() && file_exists($dir_uploads_user)) {
                    $entity->getUser()->setFoto($uploads_user);
                } else {
                    $entity->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($ordenesServicio);
    }

    /**
     * obtener las órdenes de servicio asociadas a un Profesional ID
     * @Rest\Get("/fotos/{id}", name="ordenServicio_fotos_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las fotos de servicio asociadas por ID.",
     *     @Model(type=OrdenServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las fotos de este servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del orden servicio")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Órdenes de Servicios")
     */
    public function getOrdenesServicioFotosAction(Request $request, $id)
    {
        try {
            $fotos = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicio")->findODSFotos($id);

            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/';
            $dir_uploads = $this->getParameter('dir_uploads');

            if (!is_null($fotos) && count($fotos)) {
                for ($i = 0; $i < count($fotos); $i++) {
                    if (isset($fotos[$i]['ruta']) && $fotos[$i]['ruta'] != '') {
                        $uploads_url = $uploads . $fotos[$i]['ruta'];
                        $dir_uploads_url = $dir_uploads . $fotos[$i]['ruta'];
                        if (file_exists($dir_uploads_url)) {
                            $fotos[$i]['ruta'] = $uploads_url;
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($fotos);
    }
}
