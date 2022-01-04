<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ProfesionalTarea;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfesionalTareaController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class ProfesionalTareaController extends BaseAPIController
{
    // ProfesionalTarea URI's

    /**
     * obtener las tareas asociadas a un profesional ID
     * @Rest\Get("/profesionalesTareas/{id}", name="profesional_tareas1_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las tareas asociadas a un profesional basado en parámetro ID.",
     *     @Model(type=ProfesionalTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las tareas de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")     
     *
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function getTareasProfesionalAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalTarea")->findbyProfessional($id);

            foreach ($entities as $entity) {                

                $uploads_foto = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getProfesional()->getUser()->getFoto();
                $dir_uploads_foto = $this->getParameter('dir_uploads') . $entity->getProfesional()->getUser()->getFoto();
                if ($entity->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads_foto)) {
                    $entity->getProfesional()->getUser()->setFoto($uploads_foto);
                } else {
                    $entity->getProfesional()->getUser()->setFoto('');
                }

                $uploads_icono = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getTarea()->getServicio()->getIcono();
                $dir_uploads_icono = $this->getParameter('dir_uploads') . $entity->getTarea()->getServicio()->getIcono();
                if ($entity->getTarea()->getServicio()->getIcono() && file_exists($dir_uploads_icono)) {
                    $entity->getTarea()->getServicio()->setIcono($uploads_icono);
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
     * obtener las tareas asociadas a un profesional y servicio ID
     * @Rest\Get("/profesionalesTareas/{id}/{idServicio}/", name="profesional_servicio_tareas_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las tareas asociadas a un profesional basado en parámetro ID.",
     *     @Model(type=ProfesionalTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las tareas de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")     
     * @SWG\Parameter(name="idServicio", in="path", type="integer", description="Identificador del Servicio") 
     *
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function getTareasProfesionalServicioAction(Request $request, $id, $idServicio)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalTarea")->findByProfesionalServicio($id, $idServicio);

            foreach ($entities as $entity) {                

                $uploads_foto = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getProfesional()->getUser()->getFoto();
                $dir_uploads_foto = $this->getParameter('dir_uploads') . $entity->getProfesional()->getUser()->getFoto();
                if ($entity->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads_foto)) {
                    $entity->getProfesional()->getUser()->setFoto($uploads_foto);
                } else {
                    $entity->getProfesional()->getUser()->setFoto('');
                }

                $uploads_icono = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getTarea()->getServicio()->getIcono();
                $dir_uploads_icono = $this->getParameter('dir_uploads') . $entity->getTarea()->getServicio()->getIcono();
                if ($entity->getTarea()->getServicio()->getIcono() && file_exists($dir_uploads_icono)) {
                    $entity->getTarea()->getServicio()->setIcono($uploads_icono);
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
     * obtener los profesionales asociados a una tarea ID
     * @Rest\Get("/tareas/profesionales/{id}", name="profesional_tareas2_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los profesionales asociados a una tarea basado en parámetro ID.",
     *     @Model(type=ProfesionalTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los profesionales de esta tarea"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la Tarea")
     *
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function getProfesionalesTareaAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalTarea")->findbyTask($id);

            foreach ($entities as $entity) {

                $uploads_foto = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getProfesional()->getUser()->getFoto();
                $dir_uploads_foto = $this->getParameter('dir_uploads') . $entity->getProfesional()->getUser()->getFoto();
                if ($entity->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads_foto)) {
                    $entity->getProfesional()->getUser()->setFoto($uploads_foto);
                } else {
                    $entity->getProfesional()->getUser()->setFoto('');
                }

                $uploads_icono = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getTarea()->getServicio()->getIcono();
                $dir_uploads_icono = $this->getParameter('dir_uploads') . $entity->getTarea()->getServicio()->getIcono();
                if ($entity->getTarea()->getServicio()->getIcono() && file_exists($dir_uploads_icono)) {
                    $entity->getTarea()->getServicio()->setIcono($uploads_icono);
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
     * agregar un nuevo profesional_tarea
     * @Rest\Post("/profesionalesTareas", name="profesional_tarea_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="ProfesionalTarea agregado exitosamente",
     *     @Model(type=ProfesionalTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo profesional_tarea"
     * )
     *
     * @SWG\Parameter(
     *     name="profesional_id",
     *     in="body",
     *     type="integer",
     *     description="ID del profesional",
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
     *     name="precio",
     *     in="body",
     *     type="float",
     *     description="Precio de la tarea",
     *     schema={},
     *     required=false
     * )
     *
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function addProfesionalTareaAction(Request $request)
    {
        $em = $this->em;
        try {
            $profesional_id = $request->request->get('profesional_id', null);
            $tarea_id = $request->request->get('tarea_id', null);
            $precio = $request->request->get('precio', null);

            if (is_null($profesional_id)) {
                $response['value'] = $profesional_id;
                $response['message'] = "Por favor introduzca el profesional";
                return $this->JsonResponseBadRequest($response);
            } else {
                $profesional = $em->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);
                if (!$profesional) {
                    $response['value'] = $profesional_id;
                    $response['message'] = "Profesional no encontrado";
                    return $this->JsonResponseBadRequest($response);
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
                }
            }

            $profesional_tarea = $em->getRepository('App:ProfesionalTarea')->findOneBy(array(
                'profesional' => $profesional_id,
                'tarea' => $tarea_id
            ));

            if ($profesional_tarea) {
                $response['value'] = $profesional_tarea->getId();
                $response['message'] = "Ya existe un profesional_tarea con profesional_id=$profesional_id y tarea_id=$tarea_id.";
                return $this->JsonResponseBadRequest($response);
            }

            if (!is_null($precio) && !is_numeric($precio)) {
                $response['value'] = $precio;
                $response['message'] = "El precio debe ser numérico";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($precio < 0) {
                    $response['value'] = $precio;
                    $response['message'] = "El precio debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $entity = new ProfesionalTarea();
            $entity->setProfesional($profesional);
            $entity->setTarea($tarea);
            $entity->setPrecio($precio);

            $em->persist($entity);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * actualizar tarea del profesional
     * @Rest\Put("/profesionalesTareas", name="profesional_tarea_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="tarea fue actualizada satisfactoriamente.",
     *     @Model(type=ProfesionalTarea::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar el precio de la tarea."
     * )
     *
     * @SWG\Parameter(
     *     name="profesional_id",
     *     in="body",
     *     type="integer",
     *     description="ID del profesional",
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
     *     name="precio",
     *     in="body",
     *     type="float",
     *     description="Precio de la tarea",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="estado",
     *     in="body",
     *     type="integer",
     *     description="Estado de la Tarea. Opciones: 0: Inactiva, 1: Activa",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function editProfesionalTareaAction(Request $request)
    {
        try {
            $profesional_id = $request->request->get('profesional_id', null);
            $tarea_id = $request->request->get('tarea_id', null);
            $precio = $request->request->get('precio', null);
            $estado = $request->request->get('estado', null);

            if (is_null($profesional_id)) {
                $response['value'] = $profesional_id;
                $response['message'] = "Por favor introduzca el profesional";
                return $this->JsonResponseBadRequest($response);
            } else {
                $profesional = $this->em->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);
                if (!$profesional) {
                    $response['value'] = $profesional_id;
                    $response['message'] = "Profesional no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($tarea_id)) {
                $response['value'] = $tarea_id;
                $response['message'] = "Por favor introduzca la tarea";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tarea = $this->em->getRepository("App:Tarea")->findTareaActiva($tarea_id);
                if (!$tarea) {
                    $response['value'] = $tarea_id;
                    $response['message'] = "Tarea no encontrada";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $profesional_tarea = $this->em->getRepository('App:ProfesionalTarea')->findOneBy(array(
                'profesional' => $profesional_id,
                'tarea' => $tarea_id
            ));

            if (!$profesional_tarea) {
                return $this->JsonResponseNotFound();
            }

            if (!is_null($precio) && !is_numeric($precio)) {
                $response['value'] = $precio;
                $response['message'] = "El precio debe ser numérico";
                return $this->JsonResponseBadRequest($response);
            } else {
                if ($precio < 0) {
                    $response['value'] = $precio;
                    $response['message'] = "El precio debe ser un valor positivo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($estado) && in_array(intval($estado), [0, 1])) {
                $profesional_tarea->setEstado($estado);
            }

            $profesional_tarea->setPrecio($precio);

            $this->em->persist($profesional_tarea);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($profesional_tarea, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un profesional_tarea dado profesionalID y tareaID
     * @Rest\Delete("/profesionalesTareas/{profesional_id}/{tarea_id}", name="profesional_tarea_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El profesional_tarea fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ProfesionalTarea")
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function deleteProfesionalTareaAction(Request $request, $profesional_id, $tarea_id)
    {
        try {
            $profesional_tarea = $this->em->getRepository('App:ProfesionalTarea')->findOneBy(array(
                'profesional' => $profesional_id,
                'tarea'       => $tarea_id
            ));
            if (!$profesional_tarea || is_null($profesional_tarea)) {
                return $this->JsonResponseNotFound();
            }

            $this->em->remove($profesional_tarea);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * eliminar los registros de profesional_tarea dado el profesionalID
     * @Rest\Delete("/tareas/profesionales/{id}", name="tareas_profesional_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Los registros de profesional_tarea fueron eliminados exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registros no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")
     *
     * @SWG\Tag(name="ProfesionalTarea")
     */
    public function deleteTareasProfesionalAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalTarea")->findbyProfessional($id);

            if (!$entities || is_null($entities)) {
                return $this->JsonResponseNotFound();
            }

            foreach ($entities as $profesional_tarea) {
                $this->em->remove($profesional_tarea);
                $this->em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registros eliminados con éxito!");
    }
}
