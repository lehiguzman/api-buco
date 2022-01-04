<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Servicio;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ServicioController
 *
 * @Route("/api/v1/servicios")
 * @IsGranted("ROLE_USER")
 */
class ServicioController extends BaseAPIController
{
    /**
     * Lista los servicios
     * @Rest\Get("", name="servicio_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los servicios",
     *     @Model(type=Servicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los servicios"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Servicios")
     */
    public function getAllServicioAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServicios($params);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getIcono();
                if ($entity->getIcono() && file_exists($dir_uploads)) {
                    $entity->setIcono($uploads);
                } else {
                    $entity->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista los servicios de determinado tipo de sistema
     * @Rest\Get("/sistema/{id}", name="servicio_sistema_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los servicios de determinado tipo de sistema",
     *     @Model(type=Servicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los servicios"
     * )
     *     
     * @SWG\Tag(name="Servicios")
     */
    public function getAllServicioSistemaAction(Request $request, $id)
    {
        try {

            $records = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServiciosSistema($id);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getIcono();
                if ($entity->getIcono() && file_exists($dir_uploads)) {
                    $entity->setIcono($uploads);
                } else {
                    $entity->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de un servicio dado el ID
     * @Rest\Get("/{id}", name="servicio_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el servicio basado en parámetro ID.",
     *     @Model(type=Servicio::class)
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
     *     description="El servicio ID"
     * )
     *
     *
     * @SWG\Tag(name="Servicios")
     */
    public function getServicioAction(Request $request, $id)
    {
        try {
            $servicio = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServicioActivo($id);
            if (!$servicio || is_null($servicio)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $servicio->getIcono();
        $dir_uploads = $this->getParameter('dir_uploads') . $servicio->getIcono();
        if ($servicio->getIcono() && file_exists($dir_uploads)) {
            $servicio->setIcono($uploads);
        } else {
            $servicio->setIcono('');
        }

        return $this->JsonResponseSuccess($servicio);
    }

    /**
     * agregar un nuevo servicio
     * @Rest\Post("", name="servicio_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Servicio agregado exitosamente",
     *     @Model(type=Servicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo servicio"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del servicio",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción del servicio",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del servicio, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="sistemaTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de sistema, valores permitidos: 1: bucoservicio, 2: bucotalento",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="comisionTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de comisión, valores permitidos: 1: fijo, 2: variable",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="montoComision",
     *     in="body",
     *     type="float",
     *     description="Monto de la comisión en caso de que el tipo sea fijo o variable",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="porcentajePenalizacion",
     *     in="body",
     *     type="float",
     *     description="Porcentaje de la penalización en caso de cancelacion del servicio",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="icono",
     *     in="body",
     *     type="string",
     *     description="Ruta relativa del ícono, ej: servicios/imagen.jpg",
     *     schema={},
     *     required=false
     * )
     *
     *
     * @SWG\Tag(name="Servicios")
     */
    public function addServicioAction(Request $request)
    {
        $em = $this->em;
        try {
            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $estatus = $request->request->get('estatus', 1);
            $sistemaTipo = $request->request->get('sistemaTipo', 1);
            $comisionTipo = $request->request->get('comisionTipo', null);
            $montoComision = $request->request->get('montoComision', null);
            $porcentajePenalizacion = $request->request->get('porcentajePenalizacion', null);
            $icono = $request->request->get('icono', null);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $sNombre = $em->getRepository("App:Servicio")->servicioNombre($nombre);
                if ($sNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del servicio existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($sistemaTipo)) {
                if (!($sistemaTipo == "1" || $sistemaTipo == "2")) {
                    $response['value'] = $sistemaTipo;
                    $response['message'] = "Valores permitidos del Tipo de sistema: 1: bucoservicio, 2: bucotalento";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($comisionTipo) == false) {
                $response['value'] = $comisionTipo;
                $response['message'] = "Por favor introduzca una tipo de comisión";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($comisionTipo == "1" || $comisionTipo == "2")) {
                    $response['value'] = $comisionTipo;
                    $response['message'] = "Valores permitidos del tipo de comisión: 1: Fijo, 2: Variable.";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($comisionTipo == "1") {
                        if (is_null($montoComision)) {
                            $response['value'] = $montoComision;
                            $response['message'] = "Por favor introduzca el monto de la comisión";
                            return $this->JsonResponseBadRequest($response);
                        } elseif (!is_numeric($montoComision)) {
                            $response['value'] = $montoComision;
                            $response['message'] = "El monto de la comisión debe ser numérico";
                            return $this->JsonResponseBadRequest($response);
                        } else {
                            if ($montoComision < 0) {
                                $response['value'] = $montoComision;
                                $response['message'] = "El monto de comisión debe ser un valor positivo";
                                return $this->JsonResponseBadRequest($response);
                            }
                        }
                    }
                }
            }

            if ($porcentajePenalizacion) {
                if ($porcentajePenalizacion < 0 || $porcentajePenalizacion > 100) {
                    $response['value'] = $porcentajePenalizacion;
                    $response['message'] = "El porcentaje de penalización debe ser entre 0 y 100";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $servicio = new Servicio();
            $servicio->setNombre($nombre);
            $servicio->setDescripcion($descripcion);
            $servicio->setEstatus(intval($estatus));
            $servicio->setSistemaTipo(intval($sistemaTipo));
            $servicio->setComisionTipo(intval($comisionTipo));
            $servicio->setMontoComision($montoComision);
            $servicio->setPorcentajePenalizacion(floatval($porcentajePenalizacion));
            $servicio->setIcono($icono);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($servicio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($servicio);
            $em->flush();

            // Se crea el subdirectorio para los archivos del servicio
            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $servicio->getIcono();
            $dir_uploads = $this->getParameter('dir_uploads');
            $dir = $dir_uploads . 'servicios/' . $servicio->getId() . '/';

            if (!file_exists($dir) && !is_dir($dir)) {

                mkdir($dir, 750, true);

                // Mover el archivo a su correspondiente directorio ID
                $dir_uploads_old = $this->getParameter('dir_uploads') . $servicio->getIcono();
                if ($servicio->getIcono() && file_exists($dir_uploads_old)) {
                    $filename = basename($dir_uploads_old);
                    rename($dir_uploads_old, $dir . $filename);
                    $servicio->setIcono('servicios/' . $servicio->getId() . '/' . $filename);
                    $em->persist($servicio);
                    $em->flush();
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($servicio, 201);
    }

    /**
     * actualizar la información de un servicio
     * @Rest\Put("/{id}", name="servicio_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del servicio fue actualizada satisfactoriamente.",
     *     @Model(type=Servicio::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del servicio."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID del servicio"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del servicio",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción del servicio",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del servicio, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="sistemaTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de sistema, valores permitidos: 1: bucoservicio, 2: bucotalento",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="comisionTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de comisión, valores permitidos: 1: fijo, 2: variable",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="montoComision",
     *     in="body",
     *     type="float",
     *     description="Monto de la comisión en caso de que el tipo sea fijo o variable",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="porcentajePenalizacion",
     *     in="body",
     *     type="float",
     *     description="Porcentaje de la penalización en caso de cancelacion del servicio",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="icono",
     *     in="body",
     *     type="string",
     *     description="Ruta relativa del ícono, ej: servicios/imagen.jpg",
     *     schema={},
     *     required=false
     * )
     *
     *
     * @SWG\Tag(name="Servicios")
     */
    public function editServicioAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }

            $servicio = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServicioActivo($id);
            if (!$servicio || is_null($servicio)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $estatus = $request->request->get('estatus', null);
            $sistemaTipo = $request->request->get('sistemaTipo', null);
            $comisionTipo = $request->request->get('comisionTipo', null);
            $montoComision = $request->request->get('montoComision', null);
            $porcentajePenalizacion = $request->request->get('porcentajePenalizacion', null);
            $icono = $request->request->get('icono');

            $modificar = false;
            $borrar_archivo = false;

            if (trim($nombre) && !is_null($nombre) && $servicio->getNombre() != $nombre) {
                $sNombre = $this->em->getRepository("App:Servicio")->servicioNombre($nombre, $id);
                if ($sNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del servicio existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $servicio->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (trim($descripcion) && !is_null($descripcion) && $servicio->getDescripcion() != $descripcion) {
                $servicio->setDescripcion($descripcion);
                $modificar = true;
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($servicio->getEstatus() != intval($estatus)) {
                        $servicio->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($sistemaTipo)) {
                if (!($sistemaTipo == "1" || $sistemaTipo == "2")) {
                    $response['value'] = $sistemaTipo;
                    $response['message'] = "Valores permitidos del tipo de sistema: 1: bucoservicio, 2: bucotalento";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($servicio->getSistemaTipo() != intval($sistemaTipo)) {
                        $servicio->setSistemaTipo(intval($sistemaTipo));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($comisionTipo)) {
                if (!($comisionTipo == "1" || $comisionTipo == "2")) {
                    $response['value'] = $comisionTipo;
                    $response['message'] = "Valores permitidos del tipo de comisión: 1: Fijo, 2: Variable.";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($servicio->getComisionTipo() != $comisionTipo) {
                        $servicio->setComisionTipo(intval($comisionTipo));
                        $modificar = true;
                        if ($comisionTipo == "2") $servicio->setMontoComision(0);
                    }
                }
            }

            if (($servicio->getComisionTipo() == 1)) {
                if (is_null($servicio->getMontoComision()) && is_null($montoComision)) {
                    $response['value'] = $montoComision;
                    $response['message'] = "Por favor introduzca el monto de la comisión";
                    return $this->JsonResponseBadRequest($response);
                } elseif (!is_null($montoComision)) {
                    if (!is_numeric($montoComision)) {
                        $response['value'] = $montoComision;
                        $response['message'] = "El monto de la comisión debe ser numérico";
                        return $this->JsonResponseBadRequest($response);
                    } elseif ($montoComision < 0) {
                        $response['value'] = $montoComision;
                        $response['message'] = "El monto de la comisión debe ser un valor positivo";
                        return $this->JsonResponseBadRequest($response);
                    } else {
                        if ($servicio->getMontoComision() != $montoComision || $modificar) {
                            $servicio->setMontoComision($servicio->getComisionTipo() == 2 ? null : $montoComision);
                            $modificar = true;
                        }
                    }
                }
            }

            if (!is_null($porcentajePenalizacion)) {
                if ($porcentajePenalizacion < 0 || $porcentajePenalizacion > 100) {
                    $response['value'] = $porcentajePenalizacion;
                    $response['message'] = "El porcentaje de penalización debe ser entre 0 y 100";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $servicio->setPorcentajePenalizacion(floatval($porcentajePenalizacion));
                    $modificar = true;
                }
            }

            if (!is_null($icono) && $icono != '' && $servicio->getIcono() != $icono) {
                $dir_uploads = $this->getParameter('dir_uploads') . $icono;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $icono;
                    $response['message'] = "Archivo no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $servicio->getIcono();
                    if (file_exists($dir_uploads)) {
                        $borrar_archivo = true;
                    }
                    $servicio->setIcono($icono);
                    $modificar = true;
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($servicio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {

                $this->em->persist($servicio);
                $this->em->flush();

                if ($borrar_archivo && !is_dir($dir_uploads)) {
                    unlink($dir_uploads);
                }
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($servicio, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un servicio dado el ID
     * @Rest\Delete("/{id}", name="servicio_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El servicio fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Servicio")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Servicios")
     */
    public function deleteServicioAction(Request $request, $id)
    {
        try {
            $servicio = $this->getDoctrine()->getManager()->getRepository("App:Servicio")->findServicioActivo($id);
            if (!$servicio || is_null($servicio)) {
                return $this->JsonResponseNotFound();
            }

            // Se debe eliminar los registros de ServicioDepartamento asociados a este servicio_id
            $sds = $this->getDoctrine()->getManager()->getRepository("App:ServicioDepartamento")->findbyService($id);
            foreach ($sds as $sd) {
                $this->em->remove($sd);
                $this->em->flush();
            }

            // Se debe eliminar los registros de ServicioTipoDocumento asociados a este servicio_id
            $stds = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyService($id);
            foreach ($stds as $std) {
                $this->em->remove($std);
                $this->em->flush();
            }

            $servicio->setEliminado(true);
            $servicio->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($servicio);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
