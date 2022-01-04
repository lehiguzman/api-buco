<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Profesional Calificacion
 *
 * @Route("/api/v1/profesionales")
 * @IsGranted("ROLE_USER")
 */
class ProfesionalCalificacionController extends BaseAPIController
{
    /**
     * Lista las calificaciones del profesional
     * @Rest\Get("/{id}/calificaciones", name="profesional_calificaciones_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas los profesionales con valoraciones"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las calificaciones"
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Id del Profesional")
     *
     * @SWG\Tag(name="Profesionales Calificaciones")
     */
    public function getAllCalificacionesAction($id)
    {
        try {
            $calificaciones = [];

            $profesional = $this->em->getRepository("App:Profesional")->find($id);
            $resultado = $this->em->getRepository("App:Calificacion")->findCalificacionesProfesional($id, 0, 0, 0);

            if (!is_null($profesional) && !is_null($calificaciones)) {
                $calificaciones = $resultado;
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($calificaciones);
    }
}
