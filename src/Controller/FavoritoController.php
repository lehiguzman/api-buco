<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Favorito;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FavoritoController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class FavoritoController extends BaseAPIController
{
    /**
     * obtener la lista de los favoritos
     * @Rest\Get("/favoritos/{id}", name="favorito_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los favoritos de un usuario",
     *     @Model(type=Favorito::class)
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los Favoritos"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del usuario"
     * )
     *
     * @SWG\Tag(name="Favoritos")
     */
    public function getAllFavoritoAction(Request $request, $id)
    {
        $userActivo = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($id);
        if (!$userActivo || is_null($userActivo)) {
            return $this->JsonResponseNotFound();
        }

        try {
            $records = $this->getDoctrine()->getManager()->getRepository("App:Favorito")->findByUserId($id);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getProfesional()->getUser()->getFoto();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getProfesional()->getUser()->getFoto();
                if ($entity->getProfesional()->getUser()->getFoto() && file_exists($dir_uploads)) {
                    $entity->getProfesional()->getUser()->setFoto($uploads);
                } else {
                    $entity->getProfesional()->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * agregar un favorito
     * @Rest\Post("/favoritos", name="favorito_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Favorito agregado exitosamente",
     *     @Model(type=Favorito::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrio un error agregando un nuevo favorito"
     * )
     *
     * @SWG\Parameter(
     *     name="user",
     *     in="body",
     *     type="integer",
     *     description="Id de Usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="profesional",
     *     in="body",
     *     type="integer",
     *     description="Id de profesional",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Tag(name="Favoritos")
     */
    public function addFavoritoAction(Request $request)
    {
        $em = $this->em;
        try {
            $user_id = $request->request->get('user');
            $profesional_id = $request->request->get('profesional');

            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($user_id);
            $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);

            $profesional_user = $em->getRepository('App:Favorito')->findOneBy(array(
                'profesional' => $profesional_id,
                'user' => $user_id
            ));

            if ($profesional_user) {
                $response['value'] = $profesional_user->getId();
                $response['message'] = "Ya existe un profesional_user con profesional_id=$profesional_id y user_id=$user_id.";
                return $this->JsonResponseBadRequest($response);
            }

            if (!$user) {
                $response['value'] = $user;
                $response['message'] = "El usuario no existe";

                return $this->JsonResponseBadRequest($response);
            }

            if (!$profesional) {
                $response['value'] = $profesional;
                $response['message'] = "El profesional no existe";

                return $this->JsonResponseBadRequest($response);
            }

            $favorito = new Favorito();

            $favorito->setUser($user);
            $favorito->setProfesional($profesional);

            try {
                $em->persist($favorito);
                $em->flush();
            } catch (UniqueConstraintViolationException $ex) {
                return $this->JsonResponseError($ex, 'exception');
            }
        } catch (Exception $ex) {

            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($favorito, 201);
    }

    /**
     * eliminar a un favorito dado el ID
     * @Rest\Delete("/favoritos/{id}", name="favorito_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El favorito fue eliminado exitosamente"
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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The favorito ID"
     * )
     *
     * @SWG\Tag(name="Favoritos")
     */
    public function deleteFavoritoAction(Request $request, $id)
    {
        $em = $this->em;
        try {
            $entity = $em->getRepository("App:Favorito")->find($id);
            if (!is_null($entity)) {
                $em->remove($entity);
                $em->flush();
            } else {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
