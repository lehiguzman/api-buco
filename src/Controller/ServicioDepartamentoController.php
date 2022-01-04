<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ServicioDepartamento;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ServicioDepartamentoController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class ServicioDepartamentoController extends BaseAPIController
{
    // ServicioDepartamento URI's

    /**
     * obtener los departamentos asociados a un servicio ID
     * @Rest\Get("/servicios/departamentos/{id}", name="servicio_departamento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los departamentos asociados a un servicio basado en parámetro ID.",
     *     @Model(type=ServicioDepartamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los departamentos de este servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Servicio")
     *
     *
     * @SWG\Tag(name="ServiciosDepartamentos")
     */
    public function getDepartamentosServicioAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:ServicioDepartamento")->findbyService($id);

            foreach ($entities as $entity) {

                $uploads_departamento = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getDepartamento()->getIcono();
                $dir_uploads_departamento = $this->getParameter('dir_uploads') . $entity->getDepartamento()->getIcono();
                if ($entity->getDepartamento()->getIcono() && file_exists($dir_uploads_departamento)) {
                    $entity->getDepartamento()->setIcono($uploads_departamento);
                } else {
                    $entity->getDepartamento()->setIcono('');
                }

                $uploads_servicio = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                $dir_uploads_servicio = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                if ($entity->getServicio()->getIcono() && file_exists($dir_uploads_servicio)) {
                    $entity->getServicio()->setIcono($uploads_servicio);
                } else {
                    $entity->getServicio()->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * obtener los servicios asociados a un departamento ID
     * @Rest\Get("/departamentos/servicios/{id}", name="servicios_departamento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los servicios asociados a un departamento basado en parámetro ID.",
     *     @Model(type=ServicioDepartamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los servicios de este departamento"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Departamento")
     *
     *
     * @SWG\Tag(name="ServiciosDepartamentos")
     */
    public function getServiciosDepartamentoAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:ServicioDepartamento")->findbyDepartment($id);

            foreach ($entities as $entity) {

                $uploads_departamento = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getDepartamento()->getIcono();
                $dir_uploads_departamento = $this->getParameter('dir_uploads') . $entity->getDepartamento()->getIcono();
                if ($entity->getDepartamento()->getIcono() && file_exists($dir_uploads_departamento)) {
                    $entity->getDepartamento()->setIcono($uploads_departamento);
                } else {
                    $entity->getDepartamento()->setIcono('');
                }

                $uploads_servicio = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                $dir_uploads_servicio = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                if ($entity->getServicio()->getIcono() && file_exists($dir_uploads_servicio)) {
                    $entity->getServicio()->setIcono($uploads_servicio);
                } else {
                    $entity->getServicio()->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }

    /**
     * agregar un nuevo servicio_departamento
     * @Rest\Post("/servicios/departamentos", name="servicio_departamento_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="ServicioDepartamento agregado exitosamente",
     *     @Model(type=ServicioDepartamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo servicio_departamento"
     * )
     *
     * @SWG\Parameter(
     *     name="servicio_id",
     *     in="body",
     *     type="integer",
     *     description="ID del servicio",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="departamento_id",
     *     in="body",
     *     type="integer",
     *     description="ID del departamento",
     *     schema={},
     *     required=true
     * )
     *
     *
     * @SWG\Tag(name="ServiciosDepartamentos")
     */
    public function addServicioDepartamentoAction(Request $request)
    {

        $em = $this->em;

        try {

            $servicio_id = $request->request->get('servicio_id', null);
            $departamento_id = $request->request->get('departamento_id', null);

            if (is_null($servicio_id)) {
                $response['value'] = $servicio_id;
                $response['message'] = "Por favor introduzca el servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $servicio = $em->getRepository("App:Servicio")->findServicioActivo($servicio_id);
                if (!$servicio) {
                    $response['value'] = $servicio_id;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($departamento_id)) {
                $response['value'] = $departamento_id;
                $response['message'] = "Por favor introduzca el departamento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $departamento = $em->getRepository("App:Departamento")->findDepartamentoActivo($departamento_id);
                if (!$departamento) {
                    $response['value'] = $departamento_id;
                    $response['message'] = "Departamento no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $servicio_departamento = $em->getRepository('App:ServicioDepartamento')->findOneBy(array(
                'servicio' => $servicio_id,
                'departamento' => $departamento_id
            ));

            if ($servicio_departamento) {
                $response['value'] = $servicio_departamento->getId();
                $response['message'] = "Ya existe un servicio_departamento con servicio_id=$servicio_id y departamento_id=$departamento_id.";
                return $this->JsonResponseBadRequest($response);
            }

            $entity = new ServicioDepartamento();
            $entity->setServicio($servicio);
            $entity->setDepartamento($departamento);
            $em->persist($entity);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * eliminar un servicio_departamento dado el ID
     * @Rest\Delete("/servicios/departamentos/{id}", name="servicio_departamento_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El servicio_departamento fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ServicioDepartamento")
     *
     * @SWG\Tag(name="ServiciosDepartamentos")
     */
    public function deleteServicioDepartamentoAction(Request $request, $id)
    {

        try {

            $servicio_departamento = $this->getDoctrine()->getManager()->getRepository("App:ServicioDepartamento")->find($id);
            if (!$servicio_departamento || is_null($servicio_departamento)) {
                return $this->JsonResponseNotFound();
            }

            $this->em->remove($servicio_departamento);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
