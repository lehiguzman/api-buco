<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\MetodoPago;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * MetodoPago Controller
 *
 * @Route("/api/v1/metodosPago")
 * @IsGranted("ROLE_USER")
 */
class MetodoPagoController extends BaseAPIController
{
    /**
     * Lista los métodos de pago
     * @Rest\Get("", name="metodoPago_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los métodos de pago",
     *     @Model(type=MetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los métodos de pago"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Métodos de Pago")
     */
    public function getAllMetodoPagoAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:MetodoPago")->findMetodosPago($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * agregar un nuevo método de pago
     * @Rest\Post("", name="metodoPago_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Método de pago agregado exitosamente",
     *     @Model(type=MetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo método de pago"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de pago",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="integer",
     *     description="Status del tipo de pago, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="pagoLinea",
     *     in="body",
     *     type="integer",
     *     description="Pago en línea permitido, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="requiereVuelto",
     *     in="body",
     *     type="integer",
     *     description="Requiere vuelto, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=true
     * )
     * 
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Métodos de Pago")
     */
    public function addMetodoPagoAction(Request $request)
    {
        $em = $this->em;
        try {
            $nombre = $request->request->get('nombre', null);
            $status = $request->request->get("status", 1);
            $pagoLinea = $request->request->get("pagoLinea", 0);
            $requiereVuelto = $request->request->get("requiereVuelto", 0);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del método de pago";
                return $this->JsonResponseBadRequest($response);
            } else {
                $mpNombre = $em->getRepository("App:MetodoPago")->MetodoPagoNombre($nombre);
                if ($mpNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de método de pago existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($status)) {
                if (!($status == "0" || $status == "1")) {
                    $response['value'] = $status;
                    $response['message'] = "Valores permitidos del status: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                }
            } else {
                $response['value'] = $status;
                $response['message'] = "Por favor introduzca debe indicar el status";
                return $this->JsonResponseBadRequest($response);
            }

            if (!is_null($pagoLinea)) {
                if (!($pagoLinea == "0" || $pagoLinea == "1")) {
                    $response['value'] = $pagoLinea;
                    $response['message'] = "Valores permitidos del pagoLinea: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                }
            } else {
                $response['value'] = $pagoLinea;
                $response['message'] = "Por favor introduzca debe indicar el pagoLinea";
                return $this->JsonResponseBadRequest($response);
            }

            if (!is_null($requiereVuelto)) {
                if (!($requiereVuelto == "0" || $requiereVuelto == "1")) {
                    $response['value'] = $requiereVuelto;
                    $response['message'] = "Valores permitidos del campo requiereVuelto: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                }
            } else {
                $response['value'] = $requiereVuelto;
                $response['message'] = "Por favor introduzca debe indicar si requiere Vuelto";
                return $this->JsonResponseBadRequest($response);
            }

            $metodoPago = new MetodoPago();
            $metodoPago->setNombre($nombre);
            $metodoPago->setStatus(intval($status));
            if ($pagoLinea == "1") $metodoPago->setPagoLinea(true);
            if ($requiereVuelto == "1") $metodoPago->setRequiereVuelto(true);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($metodoPago);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($metodoPago);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPago, 201);
    }

    /**
     * obtener la información de un método de pago dado el ID
     * @Rest\Get("/{id}", name="metodoPago_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el método de pago basado en parámetro ID.",
     *     @Model(type=MetodoPago::class)
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
     *     description="El método de pago ID"
     * )
     *
     *
     * @SWG\Tag(name="Métodos de Pago")
     */
    public function getMetodoPagoAction(Request $request, $id)
    {

        try {
            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPago")->findMetodoPagoActivo($id);
            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPago);
    }

    /**
     * actualizar la información de un método de pago
     * @Rest\Put("/{id}", name="metodoPago_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del método de pago fue actualizada satisfactoriamente.",
     *     @Model(type=MetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del método de pago."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID del método de pago"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del tipo de pago",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="integer",
     *     description="Status del tipo de pago, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="pagoLinea",
     *     in="body",
     *     type="integer",
     *     description="Pago en línea permitido, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="requiereVuelto",
     *     in="body",
     *     type="integer",
     *     description="Requiere Vuelto, valores permitidos: 0: No, 1: Sí",
     *     schema={},
     *     required=false
     * )
     * 
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Métodos de Pago")
     */
    public function editMetodoPagoAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPago")->findMetodoPagoActivo($id);
            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $status = $request->request->get("status", null);
            $pagoLinea = $request->request->get("pagoLinea", null);
            $requiereVuelto = $request->request->get("requiereVuelto", null);

            $modificar = false;

            if (trim($nombre) && !is_null($nombre) && $metodoPago->getNombre() != $nombre) {
                $mpNombre = $this->em->getRepository("App:MetodoPago")->MetodoPagoNombre($nombre, $id);
                if ($mpNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de método de pago existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $metodoPago->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (!is_null($status)) {
                if (!($status == "0" || $status == "1")) {
                    $response['value'] = $status;
                    $response['message'] = "Valores permitidos del status: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($metodoPago->getStatus() != intval($status)) {
                        $metodoPago->setStatus(intval($status));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($pagoLinea)) {
                if (!($pagoLinea == "0" || $pagoLinea == "1")) {
                    $response['value'] = $pagoLinea;
                    $response['message'] = "Valores permitidos del pagoLinea: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($metodoPago->getPagoLinea() != intval($pagoLinea)) {
                        $metodoPago->setPagoLinea(intval($pagoLinea));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($requiereVuelto)) {
                if (!($requiereVuelto == "0" || $requiereVuelto == "1")) {
                    $response['value'] = $requiereVuelto;
                    $response['message'] = "Valores permitidos del requiereVuelto: 0: No, 1: Sí";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($metodoPago->getRequiereVuelto() != intval($requiereVuelto)) {
                        $metodoPago->setRequiereVuelto(intval($requiereVuelto));
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($metodoPago);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($metodoPago);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPago, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un método de pago dado el ID
     * @Rest\Delete("/{id}", name="metodoPago_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El método de pago fue eliminado exitosamente"
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
     *     description="El ID del método de pago"
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Métodos de Pago")
     */
    public function deleteMetodoPagoAction(Request $request, $id)
    {
        try {
            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPago")->findMetodoPagoActivo($id);
            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }

            $metodoPago->setEliminado(true);
            $metodoPago->setFechaEliminado(new \DateTime('now'));

            $this->em->persist($metodoPago);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPago, 200, "¡Registro eliminado con éxito!");
    }
}
