<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\FormularioDinamicoServicio;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Formulario Dinámico Servicio Controller
 *
 * @Route("/api/v1/formulariosdinamicos/servicios")
 * @IsGranted("ROLE_USER")
 */
class FormularioDinamicoServicioController extends BaseAPIController
{
    /**
     * Lista todos los Formularios Dinámicos de los Servicios.
     * @Rest\Get("", name="formulariodinamico_servicio_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todos los Formularios Dinámicos Servicios.",
     *     @Model(type=FormularioDinamicoServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id, servicio, formularioDinamico, nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: servicio, formularioDinamico, nombre.")
     * @SWG\Parameter(name="idServicio", in="path", type="string", description="id del servicio.")
     *
     * @SWG\Tag(name="Formularios Dinámicos Servicios")
     * @IsGranted("ROLE_USER")
     */
    public function getAllFormularioDinamicoServicioAction(Request $request)
    {
        try {
            $params = $request->query->all();
            $idServicio = isset($params['idServicio']) ? $params['idServicio'] : null;
            // Campos con los que se podra ordenar
            $params['fields'] = ['id', 'servicio', 'formularioDinamico, nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            if ($idServicio) {
                $records = $this->em->getRepository("App:FormularioDinamicoServicio")->findFomularioDinamicoServicioID($idServicio);
            } else {
                $records = $this->em->getRepository("App:FormularioDinamicoServicio")->findFomularioDinamicoServicios($params);
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Registra un Formulario Dinámico de Servicio en el sistema.
     * @Rest\Post("", name="formulariodinamico_servicio_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Formulario Dinámico de Servicio registrado satisfactoriamente.",
     *     @Model(type=FormularioDinamicoServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     * 
     * @SWG\Parameter(
     *     name="Parámetros",
     *     in="body",
     *     required=true,
     *     description="Campos para el registro de Formulario Dinámico Servicio.",
     *     @SWG\Schema(
     *         @SWG\Property(property="servicio", type="integer"),
     *         @SWG\Property(property="formularioDinamico", type="integer"),
     *         @SWG\Property(property="nombre", type="string"),
     *         @SWG\Property(property="opciones", type="string"),
     *         @SWG\Property(property="longitudMinima", type="integer"),
     *         @SWG\Property(property="longitudMaxima", type="integer"),
     *         @SWG\Property(property="requerido", type="boolean"),
     *     )
     * )
     *
     * @SWG\Tag(name="Formularios Dinámicos Servicios")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addFormularioDinamicoServicioAction(Request $request)
    {
        try {
            $servicioID = $request->request->get("servicio", null);
            $formularioDinamicoID = $request->request->get("formularioDinamico", null);
            $nombre = $request->request->get("nombre", null);
            $opciones = $request->request->get("opciones", null);
            $longitudMinima = $request->request->get("longitudMinima", null);
            $longitudMaxima = $request->request->get("longitudMaxima", null);
            $requerido = $request->request->get("requerido", null);

            if (trim($servicioID) == false) {
                $response['value'] = $servicioID;
                $response['info'] = "Por favor introduzca servicio";

                return $this->JsonResponseBadRequest($response);
            }
            $Servicio = $this->em->getRepository("App:Servicio")->findOneBy([
                'id' => $servicioID,
                'estatus' => 1,
                'eliminado' => 0
            ]);
            if (is_null($Servicio)) {
                $response['value'] = $servicioID;
                $response['message'] = "Servicio no encontrado o no disponible";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($formularioDinamicoID) == false) {
                $response['value'] = $formularioDinamicoID;
                $response['info'] = "Por favor introduzca formularioDinamico";

                return $this->JsonResponseBadRequest($response);
            }
            $FormularioDinamico = $this->em->getRepository("App:FormularioDinamico")->findOneBy([
                'id' => $formularioDinamicoID,
                'activo' => 1
            ]);
            if (is_null($FormularioDinamico)) {
                $response['value'] = $formularioDinamicoID;
                $response['message'] = "Formulario Dinámico no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['info'] = "Por favor introduzca nombre";

                return $this->JsonResponseBadRequest($response);
            }
            $clave_nombre = $Servicio->getId() . "_" . $this->limpiarClave($nombre);
            $tempFDServ = $this->em->getRepository("App:FormularioDinamicoServicio")->findOneBy(['nombre' => $clave_nombre]);
            if (!is_null($tempFDServ)) {
                $response['value'] = $clave_nombre;
                $response['message'] = "Este campo especial ya esta configurado";
                return $this->JsonResponseBadRequest($response);
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $FDServicio = new FormularioDinamicoServicio();
            $FDServicio->setServicio($Servicio);
            $FDServicio->setFormularioDinamico($FormularioDinamico);
            $FDServicio->setNombre($nombre);
            $FDServicio->setTipo($FormularioDinamico->getTipo());
            $FDServicio->setClave($clave_nombre);
            if (!is_null($opciones) && in_array($FormularioDinamico->getTipo(), ['boolean', 'selectsimple', 'selectmultiple']) && strpos($opciones, ";")) {
                if (in_array($FormularioDinamico->getTipo(), ['boolean'])) {
                    $opc = explode(";", $opciones);
                    $opciones = "$opc[0];$opc[1]";
                    $FDServicio->setOpciones($opciones);
                } else {
                    $FDServicio->setOpciones($opciones);
                }
            }
            if (is_numeric($longitudMinima)) {
                $FDServicio->setlongitudMinima($longitudMinima);
            }
            if (is_numeric($longitudMaxima)) {
                $FDServicio->setLongitudMaxima($longitudMaxima);
            }
            if (!is_null($requerido) && (strcmp(trim($requerido), "true") === 0 || $requerido === 1)) {
                $FDServicio->setRequerido(true);
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($FDServicio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $this->em->persist($FDServicio);
            $this->em->flush();

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($FDServicio, 201);
    }

    /**
     * Obtiene los datos del Formulario Dinámico del Servicio.
     * @Rest\Get("/{id}", name="formulariodinamico_servicio_details")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene los datos del Formulario Dinámico del Servicio.",
     *     @Model(type=FormularioDinamicoServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Formulario Dinámico del Servicio")
     *
     * @SWG\Tag(name="Formularios Dinámicos Servicios")
     * @IsGranted("ROLE_USER")
     */
    public function getFormularioDinamicoServicioAction($id)
    {
        try {
            $FDServicio = $this->em->getRepository("App:FormularioDinamicoServicio")->findOneBy([
                'servicio' => $id,
                'eliminado' => 0
            ]);
            if (!$FDServicio || is_null($FDServicio)) {
                return $this->JsonResponseNotFound();
                // $FDServicio = [];
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($FDServicio);
    }

    /**
     * Actualiza los datos del Formulario Dinámico del Servicio.
     * @Rest\Put("", name="formulariodinamico_servicio_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Formulario Dinámico del Servicio actualizado satisfactoriamente.",
     *     @Model(type=FormularioDinamicoServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     *     name="Parámetros",
     *     in="body",
     *     required=true,
     *     description="Campos a editar del Formulario Dinámico Servicio.",
     *     @SWG\Schema(
     *         @SWG\Property(property="idCampoEspecial", type="string"),
     *         @SWG\Property(property="idServicio", type="string"),
     *         @SWG\Property(property="nombre", type="string"),
     *         @SWG\Property(property="opciones", type="string"),
     *         @SWG\Property(property="longitudMinima", type="integer"),
     *         @SWG\Property(property="longitudMaxima", type="integer"),
     *         @SWG\Property(property="requerido", type="boolean"),
     *     )
     * )
     *
     * @SWG\Tag(name="Formularios Dinámicos Servicios")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editFormularioDinamicoServicioAction(Request $request)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }

            $idCampoEspecial = $request->request->get("idCampoEspecial", 0);
            $idServicio = $request->request->get("idServicio", 0);

            $FDServicio = $this->em->getRepository("App:FormularioDinamicoServicio")->findFormularioDinamicoServicioActivo($idCampoEspecial, $idServicio);
            if (!$FDServicio || is_null($FDServicio)) {
                return $this->JsonResponseNotFound();
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $nombre = $request->request->get("nombre", null);
            $opciones = $request->request->get("opciones", null);
            $longitudMinima = $request->request->get("longitudMinima", null);
            $longitudMaxima = $request->request->get("longitudMaxima", null);
            $requerido = $request->request->get("requerido", null);
            $modificar = false;

            if (!is_null($nombre) && $FDServicio->getNombre() != $nombre) {
                $FDServicio->setNombre($nombre);
                $modificar = true;
            }

            if (!is_null($opciones) && $FDServicio->getOpciones() != $opciones && (in_array($FDServicio->getTipo(), ['boolean', 'selectsimple', 'selectmultiple']) && strpos($opciones, ";"))) {
                $FDServicio->setOpciones($opciones);
                $modificar = true;
            }

            if (!is_null($longitudMinima) && is_numeric($longitudMinima) && $FDServicio->getLongitudMinima() != $longitudMinima) {
                $FDServicio->setLongitudMinima($longitudMinima);
                $modificar = true;
            }

            if (!is_null($longitudMaxima) && is_numeric($longitudMaxima) && $FDServicio->getLongitudMaxima() != $longitudMaxima) {
                $FDServicio->setLongitudMaxima($longitudMaxima);
                $modificar = true;
            }

            if (!is_null($requerido)) {
                if ((strcmp(trim($requerido), "true") === 0 || $requerido === 1) && $FDServicio->getRequerido() === 0) {
                    $FDServicio->setRequerido(true);
                    $modificar = true;
                }
                if ((strcmp(trim($requerido), "false") === 0 || $requerido === 0) && $FDServicio->getRequerido() === 1) {
                    $FDServicio->setRequerido(false);
                    $modificar = true;
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($FDServicio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($FDServicio);
                $this->em->flush();

                // Confirma Transacciones a Base de Datos
                $this->em->getConnection()->commit();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($FDServicio, 200, "¡Registro modificado con éxito!");
    }

    /**
     * Elimina un Formulario Dinámico del Servicio.
     * @Rest\Delete("", name="formulariodinamico_servicio_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Formulario Dinámico del Servicio eliminado satisfactoriamente.",
     *     @Model(type=FormularioDinamicoServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     *     name="Parámetros",
     *     in="body",
     *     required=true,
     *     description="Eliminar un Formulario Dinámico Servicio.",
     *     @SWG\Schema(
     *         @SWG\Property(property="idCampoEspecial", type="string"),
     *         @SWG\Property(property="idServicio", type="string"),
     *     )
     * )
     *
     * @SWG\Tag(name="Formularios Dinámicos Servicios")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteFormularioDinamicoServicioAction(Request $request)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }

            $idCampoEspecial = $request->request->get("idCampoEspecial", 0);
            $idServicio = $request->request->get("idServicio", 0);

            $FDServicio = $this->em->getRepository("App:FormularioDinamicoServicio")->findFormularioDinamicoServicioActivo($idCampoEspecial, $idServicio);
            if (!$FDServicio || is_null($FDServicio)) {
                return $this->JsonResponseNotFound();
            }

            // eliminación lógica
            $FDServicio->setFechaEliminado(new \DateTime('now'));
            $FDServicio->setEliminado(true);

            // eliminación física
            $this->em->remove($FDServicio);

            $this->em->persist($FDServicio);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($FDServicio, 200, "¡Registro eliminado con éxito!");
    }

    private function limpiarClave($nombre)
    {
        $nombre = trim(strtolower(str_replace(" ", "_", $nombre)));
        $nombre = preg_replace("/[^A-Za-z0-9]/", '', $nombre);
        return $nombre;
    }
}
