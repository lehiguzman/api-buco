<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Comision;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ComisionController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_PROFESIONAL")
 */
class ComisionController extends BaseAPIController
{
    /**
     * Lista las Comisiones
     * @Rest\Get("/comisiones", name="comision_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las comisiones",
     *     @Model(type=Comision::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las comisiones"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Comisiones")
     */
    public function getAllComisionesAction(Request $request)
    {

        try {

            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Comision")->findComisiones($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * agregar una nueva comisión
     * @Rest\Post("/comisiones", name="comision_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Comisión agregada exitosamente",
     *     @Model(type=Comision::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar una nueva comisión"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la comisión",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de comisión. Valores permitidos: 1: Fijo, 2: Variable, 3: Combinado.",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="monto",
     *     in="body",
     *     type="float",
     *     description="Monto de la comisión en caso de que el tipo sea fijo o combinado",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="porcentaje",
     *     in="body",
     *     type="float",
     *     description="Porcentaje de la comisión en caso de que el tipo sea variable o combinado",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Comisiones")
     */
    public function addComisionAction(Request $request)
    {

        $em = $this->em;

        try {

            $nombre = $request->request->get('nombre', null);
            $tipo = $request->request->get("tipo", null);
            $monto = $request->request->get('monto', null);
            $porcentaje = $request->request->get('porcentaje', null);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre de la comisión";
                return $this->JsonResponseBadRequest($response);
            } else {
                $cNombre = $em->getRepository("App:Comision")->comisionNombre($nombre);
                if ($cNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de la comisión existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($tipo) == false) {
                $response['value'] = $tipo;
                $response['message'] = "Por favor introduzca una tipo de comisión";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($tipo == "1" || $tipo == "2" || $tipo == "3")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo de comisión: 1: Fijo, 2: Variable, 3: Combinado.";
                    return $this->JsonResponseBadRequest($response);
                } else {

                    if ($tipo == "1" || $tipo == "3") {
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
                    }

                    if ($tipo == "2" || $tipo == "3") {
                        if (is_null($porcentaje)) {
                            $response['value'] = $porcentaje;
                            $response['message'] = "Por favor introduzca el porcentaje";
                            return $this->JsonResponseBadRequest($response);
                        } elseif (!is_numeric($porcentaje)) {
                            $response['value'] = $porcentaje;
                            $response['message'] = "El porcentaje debe ser numérico";
                            return $this->JsonResponseBadRequest($response);
                        } else {
                            if ($porcentaje < 0 || $porcentaje > 100) {
                                $response['value'] = $porcentaje;
                                $response['message'] = "El porcentaje debe ser un valor entre 0 y 100";
                                return $this->JsonResponseBadRequest($response);
                            }
                        }
                    }
                }
            }

            $monto = intval($tipo) != 2 ? floatval($monto) : null;
            $porcentaje = intval($tipo) != 1 ? floatval($porcentaje) : null;
            $comision = new Comision();
            $comision->setNombre($nombre);
            $comision->setTipo(intval($tipo));
            $comision->setMonto($monto);
            $comision->setPorcentaje($porcentaje);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($comision);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($comision);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($comision, 201);
    }

    /**
     * obtener la información de una comisión dado el ID
     * @Rest\Get("/comisiones/{id}", name="comision_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la comisión basado en parámetro ID.",
     *     @Model(type=Comision::class)
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
     *     description="Id de la Comision"
     * )
     *
     * @SWG\Tag(name="Comisiones")
     */
    public function getComisionAction(Request $request, $id)
    {

        try {

            $comision = $this->getDoctrine()->getManager()->getRepository("App:Comision")->findComisionActiva($id);
            if (!$comision || is_null($comision)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($comision);
    }

    /**
     * actualizar la información de una comisión
     * @Rest\Put("/comisiones/{id}", name="comision_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La comisión fue actualizada exitosamente."
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información de la comisión."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la comisión"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la comisión",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de comisión. Valores permitidos: 1: Fijo, 2: Variable, 3: Combinado.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="monto",
     *     in="body",
     *     type="float",
     *     description="Monto de la comisión en caso de que el tipo sea fijo o combinado",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="porcentaje",
     *     in="body",
     *     type="float",
     *     description="Porcentaje de la comisión en caso de que el tipo sea variable o combinado",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Comisiones")
     */
    public function editComisionAction(Request $request, $id)
    {

        try {

            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetro a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $comision = $this->getDoctrine()->getManager()->getRepository("App:Comision")->findComisionActiva($id);
            if (!$comision || is_null($comision)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $tipo = $request->request->get("tipo", null);
            $monto = $request->request->get('monto', null);
            $porcentaje = $request->request->get('porcentaje', null);

            $modificar = false;

            if (trim($nombre) && !is_null($nombre) && $comision->getNombre() != $nombre) {
                $cNombre = $this->em->getRepository("App:Comision")->comisionNombre($nombre, $id);
                if ($cNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de la comisión existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $comision->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (!is_null($tipo)) {
                if (!($tipo == "1" || $tipo == "2" || $tipo == "3")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo de comisión: 1: Fijo, 2: Variable, 3: Combinado.";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($comision->getTipo() != $tipo) {
                        $comision->setTipo(intval($tipo));
                        $modificar = true;
                    }
                }
            }

            if (($comision->getTipo() == 1 || $comision->getTipo() == 3)) {
                if (is_null($comision->getMonto()) && is_null($monto)) {
                    $response['value'] = $monto;
                    $response['message'] = "Por favor introduzca el monto";
                    return $this->JsonResponseBadRequest($response);
                } 
                elseif (!is_null($monto)) {
                    if (!is_numeric($monto)) {
                        $response['value'] = $monto;
                        $response['message'] = "El monto debe ser numérico";
                        return $this->JsonResponseBadRequest($response);
                    } 
                    elseif ($monto < 0) {
                        $response['value'] = $monto;
                        $response['message'] = "El monto debe ser un valor positivo";
                        return $this->JsonResponseBadRequest($response);
                    } 
                    else {
                        if ($comision->getMonto() != $monto || $modificar) {
                            $comision->setMonto($comision->getTipo() == 2 ? null : $monto);
                            $comision->setPorcentaje($comision->getTipo() == 1 ? null : $porcentaje);
                            $modificar = true;
                        }
                    }
                }
            }

            if (($comision->getTipo() == 2 || $comision->getTipo() == 3)) {
                if (is_null($comision->getPorcentaje()) && is_null($porcentaje)) {
                    $response['value'] = $porcentaje;
                    $response['message'] = "Por favor introduzca el porcentaje";
                    return $this->JsonResponseBadRequest($response);
                } 
                elseif (!is_null($porcentaje)) {
                    if (!is_numeric($porcentaje)) {
                        $response['value'] = $porcentaje;
                        $response['message'] = "El porcentaje debe ser numérico";
                        return $this->JsonResponseBadRequest($response);
                    } 
                    elseif ($porcentaje < 0 || $porcentaje > 100) {
                        $response['value'] = $porcentaje;
                        $response['message'] = "El porcentaje debe ser un valor entre 0 y 100";
                        return $this->JsonResponseBadRequest($response);
                    } 
                    else {
                        if ($comision->getPorcentaje() != $porcentaje || $modificar) {
                            $comision->setMonto($comision->getTipo() == 2 ? null : $monto);
                            $comision->setPorcentaje($comision->getTipo() == 1 ? null : $porcentaje);
                            $modificar = true;
                        }
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($comision);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($comision);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($comision, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar una comisión dado el ID
     * @Rest\Delete("/comisiones/{id}", name="comision_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La comisión fue eliminada exitosamente"
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
     *     description="Id de la comisión"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Comisiones")
     */
    public function deleteComisionAction(Request $request, $id)
    {
        try {

            $comision = $this->getDoctrine()->getManager()->getRepository("App:Comision")->findComisionActiva($id);
            if (!$comision || is_null($comision)) {
                return $this->JsonResponseNotFound();
            }

            $comision->setEliminado(true);
            $comision->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($comision);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
