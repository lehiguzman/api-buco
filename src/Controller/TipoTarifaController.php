<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\TipoTarifa;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class TipoTarifaController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_ADMIN")
 */
class TipoTarifaController extends BaseAPIController
{
    // TipoTarifa URI's

    /**
     * Lista los tipos de tarifa
     * @Rest\Get("/tiposTarifa", name="tipoTarifa_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los tipos de tarifa",
     *     @Model(type=TipoTarifa::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los tipos de tarifa"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Tipos de Tarifas")
     */
    public function getAllTipoTarifaAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:TipoTarifa")->findTiposTarifa($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de un tipo de tarifa dado el ID
     * @Rest\Get("/tiposTarifa/{id}", name="tipoTarifa_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el tipo de tarifa basado en parámetro ID.",
     *     @Model(type=TipoTarifa::class)
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
     *     description="El tipo de tarifa ID"
     * )
     *
     *
     * @SWG\Tag(name="Tipos de Tarifas")
     */
    public function getTipoTarifaAction(Request $request, $id)
    {

        try {
            $tipo_tarifa = $this->getDoctrine()->getManager()->getRepository("App:TipoTarifa")->findTipoTarifaActivo($id);
            if (!$tipo_tarifa || is_null($tipo_tarifa)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_tarifa);
    }

    /**
     * agregar un nuevo tipo de tarifa
     * @Rest\Post("/tiposTarifa", name="tipoTarifa_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Tipo de tarifa agregado exitosamente",
     *     @Model(type=TipoTarifa::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo tipo de tarifa"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de tarifa",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo, valores permitidos: 1: Monto fijo, 2: Monto variable",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="valor",
     *     in="body",
     *     type="float",
     *     description="Valor del tipo de tarifa",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del tipo de tarifa, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Tipos de Tarifas")
     */
    public function addTipoTarifaAction(Request $request)
    {

        try {
            $em = $this->em;

            $nombre = $request->request->get('nombre', null);
            $tipo = $request->request->get('tipo', null);
            $valor = $request->request->get('valor', null);
            $estatus = $request->request->get('estatus', 1);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del tipo de tarifa";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tdNombre = $em->getRepository("App:TipoTarifa")->tipoTarifaNombre($nombre);
                if ($tdNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del tipo de tarifa existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($tipo)) {
                $response['value'] = $tipo;
                $response['message'] = "Por favor introduzca el tipo";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($tipo == "1" || $tipo == "2")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo: 1: Monto fijo, 2: Monto variable";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($valor) && !is_numeric($valor)) {
                $response['value'] = $valor;
                $response['message'] = "El valor debe ser numérico";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($valor < 0) {
                    $response['value'] = $valor;
                    $response['message'] = "El valor debe ser un valor positivo";
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

            $tipo_tarifa = new TipoTarifa();
            $tipo_tarifa->setNombre($nombre);
            $tipo_tarifa->setTipo(intval($tipo));
            $tipo_tarifa->setValor($valor);
            $tipo_tarifa->setEstatus(intval($estatus));

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tipo_tarifa);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($tipo_tarifa);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_tarifa, 201);
    }

    /**
     * actualizar la información de un tipo de tarifa
     * @Rest\Put("/tiposTarifa/{id}", name="tipoTarifa_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del tipo de tarifa fue actualizada satisfactoriamente.",
     *     @Model(type=TipoTarifa::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del tipo de tarifa."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID del tipo de tarifa"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de tarifa",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo, valores permitidos: 1: Monto fijo, 2: Monto variable",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="valor",
     *     in="body",
     *     type="float",
     *     description="Valor del tipo de tarifa",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del tipo de tarifa, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Tipos de Tarifas")
     */
    public function editTipoTarifaAction(Request $request, $id)
    {

        try {

            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $tipo_tarifa = $this->getDoctrine()->getManager()->getRepository("App:TipoTarifa")->findTipoTarifaActivo($id);
            if (!$tipo_tarifa || is_null($tipo_tarifa)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $tipo = $request->request->get('tipo', null);
            $valor = $request->request->get('valor', null);
            $estatus = $request->request->get('estatus', 1);

            $modificar = false;

            if (trim($nombre) && !is_null($nombre) && $tipo_tarifa->getNombre() != $nombre) {
                $tdNombre = $this->em->getRepository("App:TipoTarifa")->tipoTarifaNombre($nombre, $id);
                if ($tdNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del tipo de tarifa existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $tipo_tarifa->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (!is_null($tipo)) {
                if (!($tipo == "1" || $tipo == "2")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo: 1: Monto fijo, 2: Monto variable";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tipo_tarifa->getTipo() != intval($tipo)) {
                        $tipo_tarifa->setTipo(intval($tipo));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($valor) && !is_numeric($valor)) {
                $response['value'] = $valor;
                $response['message'] = "El valor debe ser numérico";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($valor < 0) {
                    $response['value'] = $valor;
                    $response['message'] = "El valor debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tipo_tarifa->getValor() != $valor) {
                        $tipo_tarifa->setValor($valor);
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
                    if ($tipo_tarifa->getEstatus() != intval($estatus)) {
                        $tipo_tarifa->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tipo_tarifa);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($tipo_tarifa);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tipo_tarifa, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un tipo de tarifa dado el ID
     * @Rest\Delete("/tiposTarifa/{id}", name="tipoTarifa_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El tipo de tarifa fue eliminado exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del tipo de tarifa")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Tipos de Tarifas")
     */
    public function deleteTipoTarifaAction(Request $request, $id)
    {

        try {

            $tipo_tarifa = $this->getDoctrine()->getManager()->getRepository("App:TipoTarifa")->findTipoTarifaActivo($id);
            if (!$tipo_tarifa || is_null($tipo_tarifa)) {
                return $this->JsonResponseNotFound();
            }

            // Se debe eliminar los registros de ServicioTipoDocumento asociados a este tipo_tarifa_id
            $stds = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyDocumentType($id);
            foreach ($stds as $std) {
                $this->em->remove($std);
                $this->em->flush();
            }

            $tipo_tarifa->setEliminado(true);
            $tipo_tarifa->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($tipo_tarifa);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
