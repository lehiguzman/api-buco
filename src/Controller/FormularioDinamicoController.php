<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\FormularioDinamico;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Formulario Dinámico Controller
 * solo para consultas
 *
 * @Route("/api/v1/formulariosdinamicos")
 * @IsGranted("ROLE_ADMIN")
 */
class FormularioDinamicoController extends BaseAPIController
{
    /**
     * Lista todos los Formularios Dinámicos.
     * @Rest\Get("/campos/list", name="formulariodinamico_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todos los Formularios Dinámicos."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Tag(name="Formularios Dinámicos")
     */
    public function getAllFormularioDinamicoAction()
    {
        try {
            $records = $this->em->getRepository("App:FormularioDinamico")->findBy([
                'activo' => 1
            ]);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }
}
