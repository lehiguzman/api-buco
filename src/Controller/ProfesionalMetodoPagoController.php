<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ProfesionalMetodoPago;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Profesionales Métodos de Pago Controller
 *
 * @Route("/api/v1/profesionales/metodosPago")
 * @IsGranted("ROLE_USER")
 */
class ProfesionalMetodoPagoController extends BaseAPIController
{
    /**
     * Lista todos los Métodos de Pago.
     * @Rest\Get("/lista", name="profesional_metodoPago_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todos los ProfesionalMetodoPago.",
     *     @Model(type=ProfesionalMetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Tag(name="Profesionales Métodos de Pago")
     */
    public function listAction()
    {
        try {
            $Profesional = $this->em->getRepository("App:Profesional")->findOneBy([
                'user' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Profesional) {
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->em->getRepository("App:ProfesionalMetodoPago")->findBy([
                'profesional' => $Profesional->getId()
            ]);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista todos los Métodos de Pago del Profesional.
     * @Rest\Get("/lista/{id}", name="profesional_metodoPago_listado")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todos los ProfesionalMetodoPago.",
     *     @Model(type=ProfesionalMetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")
     *
     * @SWG\Tag(name="Profesionales Métodos de Pago")
     */
    public function listadoProfesionalAction($id)
    {
        try {
            $Profesional = $this->em->getRepository("App:Profesional")->find($id);
            if (!$Profesional) {
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->em->getRepository("App:ProfesionalMetodoPago")->findBy([
                'profesional' => $Profesional->getId()
            ]);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Registra un Método de Pago.
     * @Rest\Post("", name="profesional_metodoPago_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Método de Pago agregado exitosamente",
     *     @Model(type=ProfesionalMetodoPago::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo metodo de pago de profesional"
     * )
     *
     * @SWG\Parameter(
     *     name="metodoPago_id",
     *     in="body",
     *     type="integer",
     *     description="ID del método de pago",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Tag(name="Profesionales Métodos de Pago")
     */
    public function createAction(Request $request)
    {
        try {
            $metodoPago_id = $request->request->get('metodoPago_id', null);

            $Profesional = $this->em->getRepository("App:Profesional")->findOneBy([
                'user' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Profesional) {
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($metodoPago_id)) {
                $response['value'] = $metodoPago_id;
                $response['message'] = "Por favor introduzca el Método de Pago";
                return $this->JsonResponseBadRequest($response);
            } else {
                $metodoPago = $this->em->getRepository("App:MetodoPago")->findMetodoPagoActivo($metodoPago_id);
                if (!$metodoPago) {
                    $response['value'] = $metodoPago_id;
                    $response['message'] = "Método de Pago no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $profesional_metodoPago = $this->em->getRepository('App:ProfesionalMetodoPago')->findOneBy(array(
                'profesional' => $Profesional->getId(),
                'metodoPago' => $metodoPago_id
            ));
            if ($profesional_metodoPago) {
                $response['value'] = $profesional_metodoPago->getId();
                $response['message'] = "Ya existe este Método de Pago para este Profesional.";
                return $this->JsonResponseBadRequest($response);
            }

            $entity = new ProfesionalMetodoPago();
            $entity->setProfesional($Profesional);
            $entity->setMetodoPago($metodoPago);

            $this->em->persist($entity);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * Elimina un Método de Pago.
     * @Rest\Delete("/{id}", name="profesional_metodoPago_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Método de Pago eliminado exitosamente",
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ProfesionalMetodoPago")
     *
     * @SWG\Tag(name="Profesionales Métodos de Pago")
     */
    public function deleteAction($id)
    {
        try {
            $Profesional = $this->em->getRepository("App:Profesional")->findOneBy([
                'user' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Profesional) {
                $response['message'] = "Profesional no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $ProfesionalMetodoPago = $this->em->getRepository('App:ProfesionalMetodoPago')->findOneBy(array(
                'profesional' => $Profesional->getId(),
                'metodoPago' => $id
            ));
            if (!$ProfesionalMetodoPago || is_null($ProfesionalMetodoPago)) {
                return $this->JsonResponseNotFound();
            }

            $this->em->remove($ProfesionalMetodoPago);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * eliminar los registros de profesional_metodoPago dado el profesionalID
     * @Rest\Delete("/profesionales/metodosPago/{id}", name="metodosPago_profesional_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Los registros de profesional_metodoPago fueron eliminados exitosamente"
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
     * @SWG\Tag(name="Profesionales Métodos de Pago")
     */
    public function deleteMetodosPagoProfesionalAction(Request $request, $id)
    {
        try {
            $entities = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalMetodoPago")->findbyProfesional($id);

            if (!$entities || is_null($entities)) {
                return $this->JsonResponseNotFound();
            }

            foreach ($entities as $profesional_metodoPago) {
                $this->em->remove($profesional_metodoPago);
                $this->em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registros eliminados con éxito!");
    }
}
