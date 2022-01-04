<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Autenticación Profesional Controller
 *
 * @Route("/api/v1/profesionales/auth")
 * @IsGranted("ROLE_PROFESIONAL")
 */
class ProfesionalAuthController extends BaseAPIController
{
    /**
     * Loguea al profesional en el sistema (Actualiza estado).
     * @Rest\Post("/login", name="profesional_login")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Profesional logueado exitosamente.",
     *     @Model(type=Profesional::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     * 
     * @SWG\Tag(name="Profesionales Auth")
     */
    public function loginAction(Request $request)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            $response['message'] = "¡Lo siento; no tiene permisos para iniciar sesión en esta Aplicación!";

            return $this->JsonResponseAccessDenied($response['message']);
        }

        try {
            $profesional = $this->em->getRepository("App:Profesional")->findOneByUser($this->getUser());
            $user = $this->getUser();

            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }
            if ($profesional->getEstatus() == 0 || $user->getIsActive() == 0) {
                $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                return $this->JsonResponseAccessDenied($response['message']);
            }

            $ocupado = $this->verificarDisponibilidad->verificar($profesional);
            if (!$ocupado && $profesional->getEstatus() !== 2) {  // Profesional no Penalizado
                $profesional->setEstatus(1); // Disponible
            }

            $this->em->persist($profesional);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }        

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {
            $usuarioTokens = $this->gestionTokens->usuarioPushTokens($user->getId());
            $notificacion = ['¡Has Iniciado Sesión como Profesional!', "Bienvenido a BucoApp"];

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

        return $this->JsonResponseSuccess($profesional, 200);
    }

    /**
     * Cierra sesión del profesional en el sistema (Actualiza estado).
     * @Rest\Post("/logout", name="profesional_logout")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Cierre de sesión éxitoso."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(
     *     name="Parámetros",
     *     in="body",
     *     required=false,
     *     description="pushToken del dispositivo.",
     *     @SWG\Schema(
     *         @SWG\Property(property="pushToken", type="string")
     *     )
     * )
     * 
     * @SWG\Tag(name="Profesionales Auth")
     */
    public function logoutAction(Request $request)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            $response['message'] = "¡Lo siento; no tiene permisos para iniciar en esta Aplicación!";

            return $this->JsonResponseAccessDenied($response['message']);
        }

        try {
            $profesional = $this->em->getRepository("App:Profesional")->findOneByUser($this->getUser());
            $user = $this->getUser();

            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }
            if ($profesional->getEstatus() == 0 || $user->getIsActive() == 0) {
                $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                return $this->JsonResponseAccessDenied($response['message']);
            }

            if ($profesional->getEstatus() !== 2) { // si el profesional no esta Penalizado
                $profesional->setEstatus(4); // Desconectado
            }

            // eliminar token de este usuario
            $pushToken = $request->request->get("pushToken", null);
            if (trim($pushToken) && !is_null($pushToken)) {
                $firebaseTokens = $this->em->getRepository("App:FirebaseTokens")->findOneBy(['pushToken' => trim($pushToken)]);
                if ($firebaseTokens) {
                    $this->em->remove($firebaseTokens);
                    $this->em->flush();
                }
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

            $this->em->persist($profesional);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($profesional, 200);
    }
}
