<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Firebase;
use App\Entity\FirebaseTokens;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Firebase Controller
 *
 * @Route("/api/v1/firebase")
 * @IsGranted({"ROLE_USER", "ROLE_CLIENTE", "ROLE_PROFESIONAL"})
 */
class FirebaseController extends BaseAPIController
{
    /**
     * Actualiza los tokens del Usuario
     * @Rest\Post("/tokens", name="firebase_tokens")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Datos actualizados exitosamente."
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
     *     description="idToken, refreshToken, pushToken, deviceModel",
     *     @SWG\Schema(
     *         @SWG\Property(property="idToken", type="string"),
     *         @SWG\Property(property="refreshToken", type="string"),
     *         @SWG\Property(property="pushToken", type="string"),
     *         @SWG\Property(property="pushRefreshToken", type="string"),
     *         @SWG\Property(property="deviceModel", type="string"),
     *         @SWG\Property(property="uid", type="string")
     *     )
     * )
     * @SWG\Tag(name="Firebase")
     */
    public function postTokensAction(Request $request)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            return $this->JsonResponseAccessDenied();
        }
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }
            $user = $this->getUser();

            // Control Profesional
            if ($this->isGranted('ROLE_PROFESIONAL')) {
                $profesional = $this->em->getRepository("App:Profesional")->findOneByUser($user);
                if (!$profesional || is_null($profesional)) {
                    return $this->JsonResponseNotFound();
                }
                if ($profesional->getEstatus() == 0) {
                    $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                    return $this->JsonResponseAccessDenied($response['message']);
                }
            }

            // Control Cliente
            // if ($this->isGranted('ROLE_CLIENTE')) {
            //     $cliente = $this->em->getRepository("App:Cliente")->findOneByUser($user);
            //     if (!$cliente || is_null($cliente)) {
            //         return $this->JsonResponseNotFound();
            //     }
            //     if ($cliente->getEstatus() == 0) {
            //         $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

            //         return $this->JsonResponseAccessDenied($response['message']);
            //     }
            // }

            // Control Usuario
            if ($user->getIsActive() == 0) {
                $response['message'] = "¡Lo siento; usted no se encuentra activo en el sistema!";

                return $this->JsonResponseAccessDenied($response['message']);
            }

            $idToken = $request->request->get("idToken", null);
            $refreshToken = $request->request->get("refreshToken", null);
            $pushToken = $request->request->get("pushToken", null);
            $pushRefreshToken = $request->request->get("pushRefreshToken", null);
            $deviceModel = $request->request->get("deviceModel", null);
            $uid = $request->request->get("uid", null);

            $modificar = false;
            $firebase = $this->em->getRepository("App:Firebase")->findOneByUser($user);
            if (!$firebase || is_null($firebase)) {
                $firebase = new Firebase();
                $firebase->setUser($this->getUser());
                if (trim($uid) && !is_null($uid) && $firebase->getUid() != $uid) {
                    $firebase->setUid($uid);
                }
                $modificar = true;
            }

            if (trim($idToken) && !is_null($idToken) && $firebase->getIdToken() != $idToken) {
                $firebase->setIdToken($idToken);
                $modificar = true;
            }

            if (trim($refreshToken) && !is_null($refreshToken) && $firebase->getRefreshToken() != $refreshToken) {
                $firebase->setRefreshToken($refreshToken);
                $modificar = true;
            }

            if (trim($uid) && !is_null($uid) && $firebase->getUid() != $uid) {
                $firebase->setUid($uid);
                $modificar = true;
            }

            $firebaseTokens = null;
            if (trim($pushToken) && !is_null($pushToken)) {
                $firebase->setPushToken(trim($pushToken));
                $firebaseTokens = $this->em->getRepository("App:FirebaseTokens")->findOneBy(['pushToken' => trim($pushToken)]);
                // si el token no existia previamente
                if (!$firebaseTokens) {
                    $firebaseTokens = new FirebaseTokens();
                    $firebaseTokens->setFirebase($firebase);
                    $firebaseTokens->setPushToken(trim($pushToken));
                    if (trim($pushRefreshToken) && !is_null($pushRefreshToken)) {
                        $firebaseTokens->setPushRefreshToken(trim($pushRefreshToken));
                    }
                    if (trim($deviceModel) && !is_null($deviceModel)) {
                        $firebaseTokens->setDeviceModel(trim($deviceModel));
                    }

                    // Verificar datos de la Entidad
                    $errors = $this->validator->validate($firebaseTokens);
                    if (count($errors) > 0) {
                        return $this->JsonResponseError($errors, 'validator');
                    }
                    $modificar = true;
                } else {
                    // si el token existe no es es necesario guardar
                    $firebaseTokens = null;
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($firebase);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $firebase->setUpdatedAt(new \DateTime());
                $this->em->persist($firebase);
                if ($firebaseTokens) {
                    $this->em->persist($firebaseTokens);
                }
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        if ($firebaseTokens) {
            return $this->JsonResponseSuccess($firebaseTokens);
        }
        return $this->JsonResponseSuccess($firebase);
    }

    /**
     * Gestion de Usuario en Firebase
     * @Rest\Post("/gestionar", name="firebase_gestion")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Solicitud exitosa."
     * )
     *
     * @SWG\Tag(name="Firebase")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function postGestionarAction(Request $request)
    {
        try {
            $correos = [];
            $clave = "AZl123456!";

            $response = [];
            foreach ($correos as $key => $correo) {
                $res0 = null;
                $resp1 = $this->firebase->signIn($correo, $clave, "profesional");
                if ($resp1 && key_exists('idToken', $resp1)) {
                    $resp2 = $this->firebase->deleteAccount($resp1['idToken'], "profesional");
                    $res0[$correo]['signIn'] = $resp1;
                    $res0[$correo]['deleteAccount'] = $resp2;
                }
                if ($res0) {
                    $response[] = $res0;
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponse($response);
    }
}
