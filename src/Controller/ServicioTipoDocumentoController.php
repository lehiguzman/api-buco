<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ServicioTipoDocumento;
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
 * Class ServicioTipoDocumentoController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class ServicioTipoDocumentoController extends BaseAPIController
{
    // ServicioTipoDocumento URI's

    /**
     * Lista los servicios con documentos asociados
     * @Rest\Get("/servicios/tiposDocumento/all", name="servicio_tiposDocumento_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los servicios asociados a tipo de documentos.",
     *     @Model(type=ServicioTipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los tipos de documento de este servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Servicio")
     *
     *
     * @SWG\Tag(name="ServiciosTiposDocumento")
     */
    public function getAllServicioTipoDocumentoAction(Request $request)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyServiceDocumentType();

            foreach ($entities as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                if ($entity->getServicio()->getIcono() && file_exists($dir_uploads)) {
                    $entity->getServicio()->setIcono($uploads);
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
     * obtener los tipos de documento asociados a un servicio ID
     * @Rest\Get("/servicios/tiposDocumento/{id}", name="servicio_tiposDocumento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los tipos de documento asociados a un servicio basado en parámetro ID.",
     *     @Model(type=ServicioTipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los tipos de documento de este servicio"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Servicio")
     *
     *
     * @SWG\Tag(name="ServiciosTiposDocumento")
     */
    public function getTiposDocumentoServicioAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyService($id);

            foreach ($entities as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                if ($entity->getServicio()->getIcono() && file_exists($dir_uploads)) {
                    $entity->getServicio()->setIcono($uploads);
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
     * obtener los servicios asociados a un tipoDocumento ID
     * @Rest\Get("/tiposDocumento/servicios/{id}", name="servicios_tipoDocumento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los servicios asociados a un tipo de documento basado en parámetro ID.",
     *     @Model(type=ServicioTipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los servicios de este tipo de documento"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del TipoDocumento")
     *
     *
     * @SWG\Tag(name="ServiciosTiposDocumento")
     */
    public function getServiciosTipoDocumentoAction(Request $request, $id)
    {

        try {

            $entities = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->findbyDocumentType($id);

            foreach ($entities as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getServicio()->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getServicio()->getIcono();
                if ($entity->getServicio()->getIcono() && file_exists($dir_uploads)) {
                    $entity->getServicio()->setIcono($uploads);
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
     * agregar un nuevo servicio_tipo_documento
     * @Rest\Post("/servicios/tiposDocumento", name="servicio_tipoDocumento_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="ServicioTipoDocumento agregado exitosamente",
     *     @Model(type=ServicioTipoDocumento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo servicio_tipo_documento"
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
     *     name="tipo_documento_id",
     *     in="body",
     *     type="integer",
     *     description="ID del tipo de documento",
     *     schema={},
     *     required=true
     * )
     *
     *
     * @SWG\Tag(name="ServiciosTiposDocumento")
     */
    public function addServicioTipoDocumentoAction(Request $request)
    {

        $em = $this->em;

        try {

            $servicio_id = $request->request->get('servicio_id', null);
            $tipo_documento_id = $request->request->get('tipo_documento_id', null);

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

            if (is_null($tipo_documento_id)) {
                $response['value'] = $tipo_documento_id;
                $response['message'] = "Por favor introduzca el tipo de documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tipo_documento = $em->getRepository("App:TipoDocumento")->findTipoDocumentoActivo($tipo_documento_id);
                if (!$tipo_documento) {
                    $response['value'] = $tipo_documento_id;
                    $response['message'] = "Tipo de documento no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $servicio_tipo_documento = $em->getRepository('App:ServicioTipoDocumento')->findOneBy(array(
                'servicio'      => $servicio_id,
                'tipoDocumento' => $tipo_documento_id
            ));

            if ($servicio_tipo_documento) {
                $response['value'] = $servicio_tipo_documento->getId();
                $response['message'] = "Ya existe un servicio_tipo_documento con servicio_id=$servicio_id y tipo_documento_id=$tipo_documento_id.";
                return $this->JsonResponseBadRequest($response);
            }

            $entity = new ServicioTipoDocumento();
            $entity->setServicio($servicio);
            $entity->setTipoDocumento($tipo_documento);
            $em->persist($entity);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * eliminar un servicio_tipo_documento dado el ID
     * @Rest\Delete("/servicios/tiposDocumento/{id}", name="servicio_tiposDocumento_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El servicio_tipo_documento fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ServicioTipoDocumento")
     *
     * @SWG\Tag(name="ServiciosTiposDocumento")
     */
    public function deleteServicioTipoDocumentoAction(Request $request, $id)
    {

        try {

            $servicio_tipo_documento = $this->getDoctrine()->getManager()->getRepository("App:ServicioTipoDocumento")->find($id);
            if (!$servicio_tipo_documento || is_null($servicio_tipo_documento)) {
                return $this->JsonResponseNotFound();
            }

            $this->em->remove($servicio_tipo_documento);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
