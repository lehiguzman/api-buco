<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Notificacion;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class NotificacionController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class NotificacionController extends BaseAPIController
{
    // Notificacion URI's

    /**
     * Lista las notificaciones
     * @Rest\Get("/notificaciones", name="notificacion_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las notificaciones",
     *     @Model(type=Notificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las notificaciones"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: leido o createdAt.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre del usuario.")
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function getAllNotificacionesAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['leido', 'createdAt'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Notificacion")->findNotificaciones($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de una notificación dado el ID
     * @Rest\Get("/notificaciones/{id}", name="notificacion_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la notificación basado en parámetro ID.",
     *     @Model(type=Notificacion::class)
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
     *     description="Notificacion ID"
     * )
     *
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function getNotificacionAction(Request $request, $id)
    {

        try {
            $notificacion = $this->getDoctrine()->getManager()->getRepository("App:Notificacion")->findNotificacion($id);
            if (!$notificacion || is_null($notificacion)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($notificacion);
    }

    /**
     * agregar una nueva notificación
     * @Rest\Post("/notificaciones", name="notificacion_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Notificación agregada exitosamente",
     *     @Model(type=Notificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nueva notificación"
     * )
     *
     * @SWG\Parameter(
     *     name="userId",
     *     in="body",
     *     type="integer",
     *     description="Id del usuario",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Parameter(
     *     name="asunto",
     *     in="body",
     *     type="string",
     *     description="Asunto de la notificación",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción de la notificación",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function addNotificacionAction(Request $request)
    {

        try {
            $em = $this->em;

            $userId = $request->request->get('userId', null);
            $asunto = $request->request->get('asunto', null);
            $descripcion = $request->request->get('descripcion', null);

            if (is_null($userId)) {
                $response['value'] = $userId;
                $response['message'] = "Por favor introduzca el userId";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $em->getRepository("App:User")->findUserActivo($userId);
                if (!$user) {
                    $response['value'] = $userId;
                    $response['message'] = "Usuario no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($asunto) == false) {
                $response['value'] = $asunto;
                $response['message'] = "Por favor introduzca el asunto de la notificación";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($descripcion) == false) {
                $response['value'] = $descripcion;
                $response['message'] = "Por favor introduzca la descripción de la notificación";
                return $this->JsonResponseBadRequest($response);
            }

            $notificacion = new Notificacion();
            $notificacion->setUser($user);
            $notificacion->setAsunto($asunto);
            $notificacion->setDescripcion($descripcion);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($notificacion);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($notificacion);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($notificacion, 201);
    }

    /**
     * colocar como leido de una notificación
     * @Rest\Put("/notificaciones/{id}", name="notificacion_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información de la notificación fue actualizada satisfactoriamente.",
     *     @Model(type=Notificacion::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información de la notificación."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="El ID de la notificación"
     * )
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function editNotificacionAction(Request $request, $id)
    {

        try {

            $notificacion = $this->getDoctrine()->getManager()->getRepository("App:Notificacion")->findNotificacion($id);
            if (!$notificacion || is_null($notificacion)) {
                return $this->JsonResponseNotFound();
            }

            $notificacion->setLeido(true);
            $this->em->persist($notificacion);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($notificacion, 200, "¡Registro modificado con éxito!");
    }

    /**
     * obtener las notificaciones de un usuario
     * @Rest\Get("/notificacionesUsuario/{userId}", name="notificaciones_usuario_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las notificaciones de un usuario",
     *     @Model(type=Documento::class)
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los Documentos"
     * )
     *
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="string",
     *     description="Id del usuario"
     * )
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function getNotificacionesUsuarioAction(Request $request, $userId)
    {


        try {

            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($userId);

            if (!$user || is_null($user)) {
                return $this->JsonResponseNotFound();
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:Notificacion")->findNotificacionesByUser($userId);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * eliminar una notificación dado el ID
     * @Rest\Delete("/notificaciones/{id}", name="notificacion_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La notificación fue eliminado exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la notificación")
     *
     * @SWG\Tag(name="Notificaciones")
     */
    public function deleteNotificacionAction(Request $request, $id)
    {

        try {

            $notificacion = $this->getDoctrine()->getManager()->getRepository("App:Notificacion")->findNotificacion($id);
            if (!$notificacion || is_null($notificacion)) {
                return $this->JsonResponseNotFound();
            }

            $notificacion->setEliminado(true);
            $notificacion->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($notificacion);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
