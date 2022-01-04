<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Autenticación Cliente Controller
 *
 * @Route("/api/v1/clientes/auth")
 * @IsGranted("ROLE_USER")
 */
class ClienteAuthController extends BaseAPIController
{
    /**
     * Loguea al cliente
     * @Rest\Post("/login", name="cliente_login")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Cliente logueado exitosamente.",
     *     @Model(type=Cliente::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo loguear el Cliente."
     * )
     * 
     * @SWG\Tag(name="Clientes Auth")
     */
    public function loginAction(Request $request)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            $response['message'] = "¡Lo siento; no tiene permisos para iniciar en esta Aplicación!";

            return $this->JsonResponseAccessDenied($response['message']);
        }

        try {
            $cliente = $this->getUser();

            if ($cliente->getIsActive() == 0) {
                $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                return $this->JsonResponseAccessDenied($response['message']);
            } else {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $cliente->getFoto();
                $dir_uploads = $this->getParameter('dir_uploads') . $cliente->getFoto();
                if ($cliente->getFoto() && file_exists($dir_uploads)) {
                    $cliente->setFoto($uploads);
                } else {
                    $cliente->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {
            $usuarioTokens = $this->gestionTokens->usuarioPushTokens($cliente->getId());
            $notificacion = ['¡Has Iniciado Sesión como Cliente!', "Bienvenido a BucoApp"];

            if (count($usuarioTokens) > 0) {
                $data = [];

                foreach ($usuarioTokens as $value) {
                    $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                }
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE NOTIFICACIONES */

        return $this->JsonResponseSuccess($cliente, 200);
    }

    /**
     * Cierra sesión del cliente en el sistema (Actualiza estado).
     * @Rest\Post("/logout", name="cliente_logout")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Cierre de sesión éxitoso.",
     *     @Model(type=Cliente::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Fallo al cerrar sesión de Cliente."
     * )
     * 
     * @SWG\Tag(name="Clientes Auth")
     * @IsGranted("ROLE_CLIENTE")
     */
    public function logoutAction()
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            $response['message'] = "¡Lo siento; no tiene permisos para iniciar en esta Aplicación!";

            return $this->JsonResponseAccessDenied($response['message']);
        }

        try {
            $cliente = $this->getUser();

            if ($cliente->getIsActive() == 0) {
                $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                return $this->JsonResponseAccessDenied($response['message']);
            }

            $firebase = $this->em->getRepository("App:Firebase")->findOneByUser($this->getUser());
            if ($firebase) {
                $firebase->setPushToken("---");
                $firebase->setRefreshToken("---");
                $this->em->persist($firebase);

                $firebaseTokens = $this->em->getRepository("App:FirebaseTokens")->findOneBy(['firebase' => $firebase]);
                if ($firebaseTokens) {
                    foreach ($firebaseTokens as $key => $fbToken) {
                        $this->em->remove($fbToken);
                        $this->em->flush();
                    }
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($cliente, 200);
    }
}
