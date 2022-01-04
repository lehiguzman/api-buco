<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;

use App\Entity\OrdenServicioProfesional;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * Class ServicioProfesionalController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class ProfesionalServicioController extends BaseAPIController
{
    // OrdenServicioProfesional URI's

    /**
     * obtener los servicios asociados a un Profesional por ID
     * @Rest\Get("/profesionalServicios/{id}", name="profesional_servicios_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas los servicios asociados a un profesional basado en parámetro ID.",
     *     @Model(type=ProfesionalServicio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas los servicio de este profesional"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del profesional")
     *
     *
     * @SWG\Tag(name="Profesionales | Servicios")
     */
    public function getProfesionalServiciosAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository('App:ProfesionalServicio')->findbyProfesional($id);
            foreach ($entities as $entity) {
                if ($entity->getServicio()) {
                    $icono = $entity->getServicio()->getIcono();
                    if ($icono) {
                        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $icono;
                        $dir_uploads = $this->getParameter('dir_uploads') . $icono;
                        if (file_exists($dir_uploads)) {
                            $entity->getServicio()->setIcono($uploads);
                        }
                    } else {
                        $entity->getServicio()->setIcono('');
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }
}
