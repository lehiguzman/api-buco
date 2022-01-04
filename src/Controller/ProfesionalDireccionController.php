<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Direccion;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Profesional Direccion Controller
 *
 * @Route("/api/v1/profesional/direcciones")
 * @IsGranted("ROLE_PROFESIONAL")
 */
class ProfesionalDireccionController extends BaseAPIController
{
    /**
     * Direcciones del Profesional
     * @Rest\Get("", name="profesional_direcciones_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="¡Solicitud Exitosa!",
     *     @Model(type=Direccion::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="¡Registro no encontrado!"
     * )
     * 
     *
     * @SWG\Parameter(name="predeterminada", in="path", type="integer", description="1: dirección predeterminada -- 0:lista de direcciones")
     *
     * @SWG\Tag(name="Profesional Direcciones")
     */
    public function getDireccionesAction(Request $request)
    {
        try {
            $params = $request->query->all(); 

            $user = $this->getUser();
            $profesional = $this->em->getRepository("App:Profesional")->findOneBy([
                'user' => $user->getId()
            ]);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }
            if ($profesional->getEstatus() === 0 || $profesional->getEliminado() === true) {
                return $this->JsonResponseAccessDenied();
            }

            if (isset($params['predeterminada']) && intval($params['predeterminada']) === 1) {
                $dirrPredeterminada = $this->em->getRepository("App:Direccion")->findOneBy([
                    'user'           => $user->getId(),
                    'sistemaTipo'    => 2,
                    'predeterminada' => true,
                    'eliminado'      => false
                ]);
                if ($dirrPredeterminada) {
                    return $this->JsonResponseSuccess($dirrPredeterminada);
                }   
            }

            $direcciones = $this->em->getRepository("App:Direccion")->findBy([
                'user'        => $user->getId(),
                'sistemaTipo' => 2,
                'eliminado'   => false
            ]);
            if ($direcciones) {
                foreach ($direcciones as $direccion) {
                    $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $direccion->getUser()->getFoto();
                    $dir_uploads = $this->getParameter('dir_uploads') . $direccion->getUser()->getFoto();
                    if ($direccion->getUser()->getFoto() && file_exists($dir_uploads)) {
                        $direccion->getUser()->setFoto($uploads);
                    } else {
                        $direccion->getUser()->setFoto('');
                    }
                }
            } else {
                $direcciones = [];
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direcciones);
    }
}
