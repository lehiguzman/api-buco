<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\TipoDocumento;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class TipoDocumentoController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class TipoDocumentoController extends BaseAPIController
{
    // TipoDocumento URI's

    /**
     * Lista los tipos de documento
     * @Rest\Get("/tiposDocumento", name="tipoDocumento_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los tipos de documento",
     *     @Model(type=TipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los tipos de documento"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="TiposDocumento")
     */
    public function getAllTipoDocumentoAction(Request $request)
    {

        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:TipoDocumento")->findTiposDocumento($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de un tipo de documento dado el ID
     * @Rest\Get("/tiposDocumento/{id}", name="tipoDocumento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el tipo de documento basado en parámetro ID.",
     *     @Model(type=TipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El tipo de documento ID"
     * )
     *
     *
     * @SWG\Tag(name="TiposDocumento")
     */
    public function getTipoDocumentoAction(Request $request, $id)
    {

        try {
            $tipo_documento = $this->getDoctrine()->getManager()->getRepository("App:TipoDocumento")->findTipoDocumentoActivo($id);
            if (!$tipo_documento || is_null($tipo_documento)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_documento);
    }

    /**
     * agregar un nuevo tipo de documento
     * @Rest\Post("/tiposDocumento", name="tipoDocumento_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Tipo de documento agregado exitosamente",
     *     @Model(type=TipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo tipo de documento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de documento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipoVencimiento",
     *     in="body",
     *     type="integer",
     *     description="Tipo de vencimiento, valores permitidos: 1: Fecha específica, 2: Periódicamente, 3: Nunca vence",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="periodicidad",
     *     in="body",
     *     type="integer",
     *     description="Periodicidad en caso de que el tipo de vecimiento sea 2",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="requiereVerificacion",
     *     in="body",
     *     type="integer",
     *     description="Requiere o no verificación, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="requiereCopia",
     *     in="body",
     *     type="integer",
     *     description="Requiere o no copia, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del tipo de documento, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="TiposDocumento")
     */
    public function addTipoDocumentoAction(Request $request)
    {

        $em = $this->em;

        try {

            $nombre = $request->request->get('nombre', null);
            $tipoVencimiento = $request->request->get('tipoVencimiento', null);
            $periodicidad = $request->request->get('periodicidad', null);
            $requiereVerificacion = $request->request->get('requiereVerificacion', 0);
            $requiereCopia = $request->request->get('requiereCopia', 0);
            $estatus = $request->request->get('estatus', 1);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del tipo de documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tdNombre = $em->getRepository("App:TipoDocumento")->tipoDocumentoNombre($nombre);
                if ($tdNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del tipo de documento existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($tipoVencimiento)) {
                $response['value'] = $tipoVencimiento;
                $response['message'] = "Por favor introduzca el tipo de vencimiento";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($tipoVencimiento == "1" || $tipoVencimiento == "2" || $tipoVencimiento == "3")) {
                    $response['value'] = $tipoVencimiento;
                    $response['message'] = "Valores permitidos del tipoVencimiento: 1: Fecha específica, 2: Periódicamente, 3: Nunca vence";
                    return $this->JsonResponseBadRequest($response);
                } elseif ($tipoVencimiento == "2" && (!$periodicidad || intval($periodicidad) <= 0)) {
                    $response['value'] = $periodicidad;
                    $response['message'] = "Debe indicar un valor de periodicidad mayor a cero si el tipo de vecimiento es 2 (Periódicamente)";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($requiereVerificacion)) {
                if (!($requiereVerificacion == "0" || $requiereVerificacion == "1")) {
                    $response['value'] = $requiereVerificacion;
                    $response['message'] = "Valores permitidos de requiereVerificacion: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($requiereCopia)) {
                if (!($requiereCopia == "0" || $requiereCopia == "1")) {
                    $response['value'] = $requiereCopia;
                    $response['message'] = "Valores permitidos de requiereCopia: 0: No, 1: Sí";
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

            $tipo_documento = new TipoDocumento();
            $tipo_documento->setNombre($nombre);
            $tipo_documento->setTipoVencimiento($tipoVencimiento);
            $tipo_documento->setPeriodicidad($tipoVencimiento == 2 ? intval($periodicidad) : null);
            $tipo_documento->setRequiereVerificacion(intval($requiereVerificacion) == 0 ? false : true);
            $tipo_documento->setRequiereCopia(intval($requiereCopia) == 0 ? false : true);
            $tipo_documento->setEstatus(intval($estatus));

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tipo_documento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($tipo_documento);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_documento, 201);
    }

    /**
     * actualizar la información de un tipo de documento
     * @Rest\Put("/tiposDocumento/{id}", name="tipoDocumento_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del tipo de documento fue actualizada satisfactoriamente.",
     *     @Model(type=TipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del tipo de documento."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID del tipo de documento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de documento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="tipoVencimiento",
     *     in="body",
     *     type="integer",
     *     description="Tipo de vencimiento, valores permitidos: 1: Fecha específica, 2: Periódicamente, 3: Nunca vence",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="periodicidad",
     *     in="body",
     *     type="integer",
     *     description="Periodicidad en caso de que el tipo de vecimiento sea 2",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="requiereVerificacion",
     *     in="body",
     *     type="integer",
     *     description="Requiere o no verificación, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="requiereCopia",
     *     in="body",
     *     type="integer",
     *     description="Requiere o no copia, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del tipo de documento, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="TiposDocumento")
     */
    public function editTipoDocumentoAction(Request $request, $id)
    {

        try {

            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $tipo_documento = $this->getDoctrine()->getManager()->getRepository("App:TipoDocumento")->findTipoDocumentoActivo($id);
            if (!$tipo_documento || is_null($tipo_documento)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $tipoVencimiento = $request->request->get('tipoVencimiento', null);
            $periodicidad = $request->request->get('periodicidad', null);
            $requiereVerificacion = $request->request->get('requiereVerificacion', null);
            $requiereCopia = $request->request->get('requiereCopia', null);
            $estatus = $request->request->get('estatus', null);

            $modificar = false;

            if (trim($nombre) && !is_null($nombre) && $tipo_documento->getNombre() != $nombre) {
                $tdNombre = $this->em->getRepository("App:TipoDocumento")->tipoDocumentoNombre($nombre, $id);
                if ($tdNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del tipo de documento existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $tipo_documento->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (!is_null($tipoVencimiento)) {
                if (!($tipoVencimiento == "1" || $tipoVencimiento == "2" || $tipoVencimiento == "3")) {
                    $response['value'] = $tipoVencimiento;
                    $response['message'] = "Valores permitidos del tipoVencimiento: 1: Fecha específica, 2: Periódicamente, 3: Nunca vence";
                    return $this->JsonResponseBadRequest($response);
                } elseif ($tipoVencimiento == "2" && (is_null($periodicidad) || !$periodicidad || intval($periodicidad) <= 0) && $tipo_documento->getTipoVencimiento() != intval($tipoVencimiento)) {
                    $response['value'] = $periodicidad;
                    $response['message'] = "Debe indicar un valor de periodicidad mayor a cero si el tipo de vecimiento es 2 (Periódicamente)";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tipo_documento->getTipoVencimiento() != intval($tipoVencimiento)) {
                        $tipo_documento->setTipoVencimiento(intval($tipoVencimiento));
                        $tipo_documento->setPeriodicidad($tipo_documento->getTipoVencimiento() == 2 ? intval($periodicidad) : null);
                        $modificar = true;
                    }
                }
            }

            if (!is_null($requiereVerificacion)) {
                if (!($requiereVerificacion == "0" || $requiereVerificacion == "1")) {
                    $response['value'] = $requiereVerificacion;
                    $response['message'] = "Valores permitidos de requiereVerificacion: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $requiereVerificacion = $requiereVerificacion == "0" ? false : true;
                    if ($tipo_documento->getRequiereVerificacion() != $requiereVerificacion) {
                        $tipo_documento->setRequiereVerificacion($requiereVerificacion);
                        $modificar = true;
                    }
                }
            }

            if (!is_null($requiereCopia)) {
                if (!($requiereCopia == "0" || $requiereCopia == "1")) {
                    $response['value'] = $requiereCopia;
                    $response['message'] = "Valores permitidos de requiereCopia: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $requiereCopia = $requiereCopia == "0" ? false : true;
                    if ($tipo_documento->getRequiereCopia() != $requiereCopia) {
                        $tipo_documento->setRequiereCopia($requiereCopia);
                        $modificar = true;
                    }
                }
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tipo_documento->getEstatus() != intval($estatus)) {
                        $tipo_documento->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tipo_documento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($tipo_documento);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_documento, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un tipo de documento dado el ID
     * @Rest\Delete("/tiposDocumento/{id}", name="tipoDocumento_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El tipo de documento fue eliminado exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del tipo de documento")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="TiposDocumento")
     */
    public function deleteTipoDocumentoAction(Request $request, $id)
    {

        try {

            $tipo_documento = $this->getDoctrine()->getManager()->getRepository("App:TipoDocumento")->findTipoDocumentoActivo($id);
            if (!$tipo_documento || is_null($tipo_documento)) {
                return $this->JsonResponseNotFound();
            }

            // Se debe eliminar los registros de ServicioTipoDocumento asociados a este tipo_documento_id
            $stds = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyDocumentType($id);
            foreach ($stds as $std) {
                $this->em->remove($std);
                $this->em->flush();
            }

            $tipo_documento->setEliminado(true);
            $tipo_documento->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($tipo_documento);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
