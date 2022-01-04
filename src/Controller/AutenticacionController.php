<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Firebase;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// CryptoJSAES
use App\Service\CryptoJSAES\CryptoJSAES;

/**
 * Class AutenticacionController
 *
 * @Route("/api")
 */
class AutenticacionController extends BaseAPIController
{
    /**
     * validar las creenciales de un usuario
     * @Rest\Post("/login_check", name="user_login_check")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El usuario fue loggeado exitosamente",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Usuario no fue loggeado existosamente"
     * )
     *
     * @SWG\Parameter(
     *     name="_username",
     *     in="body",
     *     type="string",
     *     description="Nombre de usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_password",
     *     in="body",
     *     type="string",
     *     description="Contraseña",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Autenticación")
     */
    public function getLoginCheckAction()
    { }

    /**
     * registrar un usuario con ROLE_USER
     * @Rest\Post("/registro", name="user_register")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Usuario fue registrado exitosamente",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Usuario no fue registrado exitosamente"
     * )
     *
     * @SWG\Parameter(
     *     name="_name",
     *     in="body",
     *     type="string",
     *     description="Nombre del usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_email",
     *     in="body",
     *     type="string",
     *     description="Correo electrónico del usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_username",
     *     in="body",
     *     type="string",
     *     description="Nombre de usuario (Utilizado para iniciar sesión)",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_password",
     *     in="body",
     *     type="string",
     *     description="Clave del usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Autenticación")
     */
    public function registerAction(Request $request)
    {
        $em = $this->em;

        try {
            $name = $request->request->get('_name');
            $email = $request->request->get('_email');
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            if (trim($name) == false) {
                $response['value'] = $name;
                $response['message'] = "Por favor introduzca un nombre";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($email) == false) {
                $response['value'] = $email;
                $response['message'] = "Por favor introduzca un email";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($username) == false) {
                $response['value'] = $username;
                $response['message'] = "Por favor introduzca un nombre de usuario";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($password) == false) {
                $response['value'] = $password;
                $response['message'] = "Por favor introduzca una clave";

                return $this->JsonResponseBadRequest($response);
            }

            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPlainPassword($password);
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setPasswordEncrypted(CryptoJSAES::encrypt($password, $this->getParameter('secretkey')));

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($user);
            $em->flush();

            // Se crea el subdirectorio para los archivos del usuario
            $dir_uploads = $this->getParameter('dir_uploads');
            $dir = $dir_uploads . 'profile/' . $user->getId() . '/';
            if (!file_exists($dir) && !is_dir($dir)) {
                mkdir($dir, 750, true);

                // Mover el archivo a su correspondiente directorio ID
                $dir_uploads_old = $this->getParameter('dir_uploads') . $user->getFoto();
                if ($user->getFoto() && file_exists($dir_uploads_old)) {
                    $filename = basename($dir_uploads_old);
                    rename($dir_uploads_old, $dir . $filename);
                    $user->setFoto('profile/' . $user->getId() . '/' . $filename);
                    $em->persist($user);
                    $em->flush();
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - REGISTRO USUARIO FIREBASE */
        try {
            $responseFB = $this->firebase->signUp($email, $password);
            if ($this->firebase->getStatus() == 200) {
                $fb = new Firebase();
                $fb->setUser($user);
                $fb->setUid($responseFB['localId']);
                $fb->setRefreshToken($responseFB['refreshToken']);
                $fb->setIdToken($responseFB['idToken']);
                $fb->setCreatedAt(new \DateTime('now'));
                $fb->setUpdatedAt(new \DateTime('now'));

                $em->persist($user);
                $em->persist($fb);
                $em->flush();
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - REGISTRO USUARIO FIREBASE */

        return $this->JsonResponseSuccess($user, 201);
    }

    /**
     * generar un código de reseteo de contraseña y enviarlo al correo dado
     * @Rest\Post("/password_email", name="user_password_email")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Email enviado exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error enviando el email"
     * )
     *
     *
     * @SWG\Parameter(
     *     name="_email",
     *     in="body",
     *     type="string",
     *     description="Correo electrónico del usuario",
     *     schema={},
     *     required=true
     * )
     *
     *
     * @SWG\Tag(name="Autenticación")
     */
    public function passwordEmailAction(Request $request, \Swift_Mailer $mailer)
    {
        try {
            $email = $request->request->get('_email', null);

            if (trim($email) == false) {
                $response['value'] = $email;
                $response['message'] = "Por favor introduzca un correo";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $this->em->getRepository("App:User")->findOneByEmail($email);
                if (is_null($user)) {
                    $response['value'] = $email;
                    $response['message'] = "No existe usuario con este correo electrónico";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            // Generar codigo
            $key = rand(1000, 9999);

            $user->setPasswordKey($key);
            $user->setPasswordDate(new \DateTime('now'));

            $this->em->persist($user);
            $this->em->flush();

            // ----- Mailer -----
            $setTo = $email;

            $swiftMessage = (new \Swift_Message())
                ->setSubject('Recuperar Acceso')
                ->setFrom($this->getParameter('email.sendfrom'))
                ->setTo($setTo)
                ->setBody($this->renderView(
                    'email/resetPassword.html.twig',
                    ['code' => $key]
                ), 'text/html');

            $mailer->send($swiftMessage);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Correo enviado con el código de validación!");
    }

    /**
     * el restablecimiento de la contraseña
     * @Rest\Post("/password_reset", name="user_password_reset")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Clave cambiada exitosamente",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error cambiando la clave del usuario"
     * )
     *
     * @SWG\Parameter(
     *     name="_email",
     *     in="body",
     *     type="string",
     *     description="Correo electrónico del usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_code",
     *     in="body",
     *     type="string",
     *     description="Código enviado al correo del usuario",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="_password",
     *     in="query",
     *     type="string",
     *     description="Nueva Clave del usuario",
     *     required=true
     * )
     *
     * @SWG\Tag(name="Autenticación")
     */
    public function passwordResetAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        try {
            $email = $request->request->get('_email', null);
            $key = $request->request->get('_code', null);
            $password = $request->request->get('_password', null);

            if (trim($email) == false) {
                $response['value'] = $email;
                $response['message'] = "Por favor introduzca un correo";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $this->em->getRepository("App:User")->findOneByEmail($email);
                if (is_null($user)) {
                    $response['value'] = $email;
                    $response['message'] = "No existe usuario con este correo electrónico";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            // Validar Codigo enviado al correo
            if (is_null($user->getPasswordKey()) || $key != $user->getPasswordKey()) {
                $response['value'] = $key;
                $response['message'] = "Código inválido";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($password) == false) {
                $response['value'] = $password;
                $response['message'] = "Por favor introduzca una clave";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (strlen(trim($password)) < 8) {
                    $response['value'] = $password;
                    $response['message'] = "La clave no puede ser menor a 8 caracteres";
                    return $this->JsonResponseBadRequest($response);
                }
                if (!preg_match('/([A-Z])/', trim($password))) {
                    $response['value'] = $password;
                    $response['message'] = "La clave debe tener al menos una mayúscula";
                    return $this->JsonResponseBadRequest($response);
                }
                if (!preg_match('/([0-9])/', trim($password))) {
                    $response['value'] = $password;
                    $response['message'] = "La clave debe tener al menos un número";
                    return $this->JsonResponseBadRequest($response);
                }
                if (!preg_match('/([!,%,&,@,#,$,^,*,?,_,~])/', trim($password))) {
                    $response['value'] = $password;
                    $response['message'] = "La clave debe tener al menos un caracter especial";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $secretKey = $this->getParameter('secretkey');

            // Password encrypted anterior
            $password_encrypted_old = $user->getPasswordEncrypted();

            $user->setPlainPassword($password);
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->setPasswordEncrypted(CryptoJSAES::encrypt($password, $secretKey));
            $user->setPasswordKey(null);
            $user->setPasswordDate(null);

            if (!($user->getUsername() == 'superadmin' || $user->getUsername() == 'admin')) {
                // Password anterior
                $password_old = $password_encrypted_old ? CryptoJSAES::decrypt($password_encrypted_old, $secretKey) : '';

                $responseFB = $this->firebase->signIn($user->getEmail(), $password_old);

                $detec = false;
                if (key_exists('error', $responseFB)) {
                    $errorsFB = $responseFB['error'];
                    foreach ($errorsFB['errors'] as $e) {
                        if (strcmp($e['message'], 'EMAIL_NOT_FOUND') == 0) {
                            $detec = true;
                            // Registrar correo
                            $respFB = $this->firebase->signUp($email, $password);
                        }
                    }
                }

                if (!$detec) {
                    // Cambio de contraseña en Firebase
                    $responseFB = $this->firebase->changePassword($responseFB['idToken'], $password);
                }

                if ($this->firebase->getStatus() != 200 && !$detec) {
                    $response['value'] = $user->getEmal();
                    $response['message'] = "Error cambiando la contraseña en firebase: " . $responseFB["error"]["message"];

                    return $this->JsonResponseBadRequest($response);
                }
            }

            $this->em->persist($user);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($user, 200, "¡Cambio de clave realizado con éxito!");
    }

    /**
     * Identificar Usuario Logueado
     * @Rest\Get("/v1", name="auth_api")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Usuario logueado identificado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error al obtener el usuario loggeado"
     * )
     */
    public function getUsuarioAction(Request $request)
    {
        $usuario = $this->em->getRepository("App:User")->loadUserByUsername($this->getUser()->getUsername());
        if (!$usuario || is_null($usuario)) {
            return $this->JsonResponseNotFound();
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $usuario->getFoto();
        $dir_uploads = $this->getParameter('dir_uploads') . $usuario->getFoto();
        if ($usuario->getFoto() && file_exists($dir_uploads)) {
            $usuario->setFoto($uploads);
        } else {
            $usuario->setFoto('');
        }

        return $this->JsonResponseSuccess($usuario);
    }
}
