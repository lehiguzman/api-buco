<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Tarea;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TareaController
 *
 * @Route("/api/v1/tareas")
 * @IsGranted("ROLE_USER")
 */
class TareaController extends BaseAPIController
{
    /**
     * Lista las tareas
     * @Rest\Get("", name="tarea_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las tareas",
     *     @Model(type=Tarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las tareas"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     * @SWG\Parameter(name="idServicio", in="path", type="string", description="Id del Servicio.")
     *
     * @SWG\Tag(name="Tareas")
     */
    public function getAllTareaAction(Request $request)
    {
        try {
            $params = $request->query->all();
            $servicioID = isset($params['idServicio']) ? $params['idServicio'] : null;
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            if ($servicioID) {
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
                $records = $this->em->getRepository("App:Tarea")->findBy([
                    'servicio' => $servicioID,
                    'estatus' => 1,
                    'eliminado' => 0,
                ]);
            } else {
                $records = $this->em->getRepository("App:Tarea")->findTareas($params);
                foreach ($records as $entity) {
                    $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                    $dir_uploads = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                    if ($entity->getServicio()->getIcono() && file_exists($dir_uploads)) {
                        $entity->getServicio()->setIcono($uploads);
                    } else {
                        $entity->getServicio()->setIcono('');
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de una tarea
     * @Rest\Get("/{id}", name="tarea_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la tarea basado en parámetro ID.",
     *     @Model(type=Tarea::class)
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
     *     type="string",
     *     description="La tarea ID"
     * )
     *
     *
     * @SWG\Tag(name="Tareas")
     */
    public function getTareaAction(Request $request, $id)
    {
        try {
            $tarea = $this->em->getRepository("App:Tarea")->findTareaActiva($id);
            if (!$tarea || is_null($tarea)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $tarea->getServicio()->getIcono();
        $dir_uploads = $this->getParameter('dir_uploads') . $tarea->getServicio()->getIcono();
        if ($tarea->getServicio()->getIcono() && file_exists($dir_uploads)) {
            $tarea->getServicio()->setIcono($uploads);
        } else {
            $tarea->getServicio()->setIcono('');
        }

        return $this->JsonResponseSuccess($tarea);
    }

    /**
     * agregar una nueva tarea
     * @Rest\Post("", name="tarea_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Tarea agregada exitosamente",
     *     @Model(type=Tarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar una nueva tarea"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la tarea",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus de la tarea, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="servicio",
     *     in="body",
     *     type="integer",
     *     description="Id del servicio de la tarea",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Tareas")
     */
    public function addTareaAction(Request $request)
    {
        $em = $this->em;
        try {
            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $tarifaMinima = $request->request->get('tarifaMinima', null);
            $tarifaMaxima = $request->request->get('tarifaMaxima', null);
            $estatus = $request->request->get('estatus', 1);
            $servicioID = $request->request->get('servicio', null);

            if (is_null($servicioID)) {
                $response['value'] = $servicioID;
                $response['message'] = "Por favor introduzca el servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $servicio = $em->getRepository("App:Servicio")->findServicioActivo($servicioID);
                if (!$servicio) {
                    $response['value'] = $servicioID;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre de la tarea";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tNombre = $em->getRepository("App:Tarea")->tareaNombre($nombre, $servicioID);
                if ($tNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de la tarea existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($tarifaMinima)) {
                $response['value'] = $tarifaMinima;
                $response['message'] = "Por favor introduzca la tarifa minima";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_numeric($tarifaMinima)) {
                $response['value'] = $tarifaMinima;
                $response['message'] = "El tarifa minima debe ser numérica";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($tarifaMinima < 0) {
                    $response['value'] = $tarifaMinima;
                    $response['message'] = "La tarifa minima debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($tarifaMaxima)) {
                $response['value'] = $tarifaMaxima;
                $response['message'] = "Por favor introduzca la tarifa maxima";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_numeric($tarifaMaxima)) {
                $response['value'] = $tarifaMaxima;
                $response['message'] = "La tarifa maxima debe ser numérica";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($tarifaMaxima < 0) {
                    $response['value'] = $tarifaMaxima;
                    $response['message'] = "La tarifa maxima debe ser un valor positivo";
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

            $tarea = new Tarea();
            $tarea->setNombre($nombre);
            $tarea->setDescripcion($descripcion);
            $tarea->setEstatus(intval($estatus));
            $tarea->setTarifaMinima($tarifaMinima);
            $tarea->setTarifaMaxima($tarifaMaxima);
            $tarea->setServicio($servicio);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tarea);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($tarea);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tarea, 201);
    }

    /**
     * actualizar la información de una tarea
     * @Rest\Put("/{id}", name="tarea_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información de la tarea fue actualizada satisfactoriamente.",
     *     @Model(type=Tarea::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información de la tarea."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID de la tarea"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus de la tarea, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="servicio",
     *     in="body",
     *     type="integer",
     *     description="Id del servicio de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Tareas")
     */
    public function editTareaAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $tarea = $this->em->getRepository("App:Tarea")->findTareaActiva($id);
            if (!$tarea || is_null($tarea)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $tarifaMinima = $request->request->get('tarifaMinima', null);
            $tarifaMaxima = $request->request->get('tarifaMaxima', null);
            $estatus = $request->request->get('estatus', null);
            $servicioID = $request->request->get('servicio');

            $modificar = false;

            if (trim($descripcion) && !is_null($descripcion) && $tarea->getDescripcion() != $descripcion) {
                $tarea->setDescripcion($descripcion);
                $modificar = true;
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tarea->getEstatus() != intval($estatus)) {
                        $tarea->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            if (is_null($tarea->getTarifaMinima()) && is_null($tarifaMinima)) {
                $response['value'] = $tarifaMinima;
                $response['message'] = "Por favor introduzca la tarifa minima";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_null($tarifaMinima)) {
                if (!is_numeric($tarifaMinima)) {
                    $response['value'] = $tarifaMinima;
                    $response['message'] = "La tarifa minima debe ser numérica";
                    return $this->JsonResponseBadRequest($response);
                } elseif ($tarifaMinima < 0) {
                    $response['value'] = $tarifaMinima;
                    $response['message'] = "La tarifa minima debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tarea->getTarifaMinima() != $tarifaMinima || $modificar) {
                        $tarea->setTarifaMinima($tarifaMinima);
                        $modificar = true;
                    }
                }
            }

            if (is_null($tarea->getTarifaMaxima()) && is_null($tarifaMaxima)) {
                $response['value'] = $tarifaMaxima;
                $response['message'] = "Por favor introduzca la tarifa maxima";
                return $this->JsonResponseBadRequest($response);
            } elseif (!is_null($tarifaMaxima)) {
                if (!is_numeric($tarifaMaxima)) {
                    $response['value'] = $tarifaMaxima;
                    $response['message'] = "La tarifa maxima debe ser numérica";
                    return $this->JsonResponseBadRequest($response);
                } elseif ($tarifaMaxima < 0) {
                    $response['value'] = $tarifaMaxima;
                    $response['message'] = "La tarifa maxima debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tarea->getTarifaMaxima() != $tarifaMaxima || $modificar) {
                        $tarea->setTarifaMaxima($tarifaMaxima);
                        $modificar = true;
                    }
                }
            }

            if (!is_null($servicioID)) {
                $servicio = $this->em->getRepository("App:Servicio")->findServicioActivo($servicioID);
                if (!$servicio) {
                    $response['value'] = $servicioID;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($tarea->getServicio() != $servicio) {
                        $tarea->setServicio($servicio);
                        $modificar = true;
                    }
                }
            }

            if (trim($nombre) && !is_null($nombre) && $tarea->getNombre() != $nombre) {
                $tNombre = $this->em->getRepository("App:Tarea")->tareaNombre($nombre, $tarea->getServicio()->getId(), $id);
                if ($tNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de la tarea existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $tarea->setNombre($nombre);
                    $modificar = true;
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($tarea);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($tarea);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($tarea, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar una tarea
     * @Rest\Delete("/{id}", name="tarea_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La tarea fue eliminada exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la Tarea")
     *
     * @SWG\Tag(name="Tareas")
     */
    public function deleteTareaAction(Request $request, $id)
    {
        try {
            $tarea = $this->em->getRepository("App:Tarea")->findTareaActiva($id);
            if (!$tarea || is_null($tarea)) {
                return $this->JsonResponseNotFound();
            }

            $tarea->setEliminado(true);
            $tarea->setFechaEliminado(new \DateTime('now'));

            $this->em->persist($tarea);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
