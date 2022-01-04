<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Firebase;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// CryptoJSAES
use App\Service\CryptoJSAES\CryptoJSAES;

/**
 * Class UserController
 *
 * @Route("/api/v1")
 */
class UserController extends BaseAPIController
{
    // User URI's

    /**
     * Lista los usuarios
     * @Rest\Get("/usuarios", name="user_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los usuarios",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error obteniendo todos los usuarios"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id, name, username o email.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: name, username o email.")     
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Usuarios")
     */
    public function getAllUserAction(Request $request)
    {

        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', '_name', '_email', '_username'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:User")->findUsers($params);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getFoto();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getFoto();
                if ($entity->getFoto() && file_exists($dir_uploads)) {
                    $entity->setFoto($uploads);
                } else {
                    $entity->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de un usuario dado el ID del usuario
     * @Rest\Get("/usuarios/direcciones/{id}", name="user_list_direccion")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la información de direcciones del usuario basado en el parámetro ID.",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="El usuario con el parámetro ID no existe."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id del usuario"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Usuarios")
     */
    public function getAllDireccionesByUserAction(Request $request, $id)
    {
        try {
            $direccionUser = $this->em->getRepository("App:Direccion")->findDireccionUser($id);

            /*if (!$direccionUser || is_null($direccionUser)) {
                return $this->JsonResponseNotFound();
            }*/
        } catch (Exception $ex) {

            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direccionUser);
    }

    /**
     * agregar un nuevo usuario
     * @Rest\Post("/usuarios", name="user_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Usuario agregado existosamente",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo usuario"
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
     * @SWG\Parameter(
     *     name="_rol",
     *     in="body",
     *     type="string",
     *     description="Rol del usuario, valores permitidos: ROLE_USER,ROLE_ADMIN",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="genero",
     *     in="body",
     *     type="integer",
     *     description="Genero del usuario, valores permitidos: 1: Femenino, 2: Masculino",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaNacimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de nacimiento del usuario en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="foto",
     *     in="body",
     *     type="integer",
     *     description="Ruta del archivo foto del usuario",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Usuarios")
     */
    public function addUserAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->em;
        try {
            $name = $request->request->get('_name', null);
            $email = $request->request->get('_email', null);
            $username = $request->request->get('_username', null);
            $password = $request->request->get('_password', null);
            $rol = $request->request->get('_rol', null);
            $genero = $request->request->get('genero', 2);
            $fechaNacimiento = $request->request->get("fechaNacimiento", null);
            $foto = $request->request->get('foto', null);

            // Validaciones
            if (trim($name) == false) {
                $response['value'] = $name;
                $response['message'] = "Por favor introduzca un nombre";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($email) == false) {
                $response['value'] = $email;
                $response['message'] = "Por favor introduzca un correo";
                return $this->JsonResponseBadRequest($response);
            } else {
                $usMail = $em->getRepository("App:User")->findOneByEmail($email);
                if ($usMail) {
                    $response['value'] = $email;
                    $response['message'] = "El correo ya se encuentra en uso";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($username) == false) {
                $response['value'] = $username;
                $response['message'] = "Por favor introduzca un username";
                return $this->JsonResponseBadRequest($response);
            } else {
                $usUsername = $em->getRepository("App:User")->findOneByUsername($username);
                if ($usUsername) {
                    $response['value'] = $username;
                    $response['message'] = "Username ya se encuentra en uso";
                    return $this->JsonResponseBadRequest($response);
                }
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

            if (trim($rol) == false) {
                $response['value'] = $rol;
                $response['message'] = "Por favor introduzca el rol";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($rol == "ROLE_USER" || $rol == "ROLE_ADMIN")) {
                    $response['value'] = $rol;
                    $response['message'] = "Valores permitidos del ROL: ROLE_USER, ROLE_ADMIN";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if ($genero) {
                if (!($genero == "1" || $genero == "2")) {
                    $response['value'] = $genero;
                    $response['message'] = "Valores permitidos del género: 1: Femenino, 2: Masculino";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($fechaNacimiento) && $fechaNacimiento != '') {
                $d = \DateTime::createFromFormat("Y-m-d", $fechaNacimiento);
                if (!($d && $d->format("Y-m-d") === $fechaNacimiento)) {
                    $response['value'] = $fechaNacimiento;
                    $response['message'] = "La fecha de nacimiento debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $fechaNacimiento = new \DateTime($fechaNacimiento);
                }
            }

            if (!is_null($foto) && $foto != '') {
                $dir_uploads = $this->getParameter('dir_uploads') . $foto;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $foto;
                    $response['message'] = "Archivo no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPlainPassword($password);
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->setPasswordEncrypted(CryptoJSAES::encrypt($password, $this->getParameter('secretkey')));
            $user->setRoles($rol);
            $user->setGenero(intval($genero));
            $user->setFechaNacimiento($fechaNacimiento);
            $user->setFoto($foto);
            $user->setIsActive(true);

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
     * obtener la información de un usuario dado el ID
     * @Rest\Get("/usuarios/{id}", name="user_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la información del usuario basado en el parámetro ID.",
     *     @Model(type=User::class)
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Usuario")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Usuarios")
     */
    public function getUserAction(Request $request, $id)
    {
        try {
            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($id);

            if (!$user || is_null($user)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $user->getFoto();
        $dir_uploads = $this->getParameter('dir_uploads') . $user->getFoto();
        if ($user->getFoto() && file_exists($dir_uploads)) {
            $user->setFoto($uploads);
        } else {
            $user->setFoto('');
        }

        return $this->JsonResponseSuccess($user);
    }

    /**
     * actualizar la información de un usuario
     * @Rest\Put("/usuarios/{id}", name="user_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del usuario fue actualizada exitosamente.",
     *     @Model(type=User::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *     
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del usuario."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID del usuario"
     * )
     *
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="Nombre del usuario",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="rol",
     *     in="body",
     *     type="string",
     *     description="Rol del usuario, valores permitidos: ROLE_USER,ROLE_ADMIN",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="genero",
     *     in="body",
     *     type="integer",
     *     description="Genero del usuario, valores permitidos: 1: Femenino, 2: Masculino",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaNacimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de nacimiento del usuario en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="ruta de archivo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="archivo",
     *     in="body",
     *     type="string",
     *     description="archivo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="nombreArchivo",
     *     in="body",
     *     type="string",
     *     description="nombre del archivo",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="boolean",
     *     description="Estatus del usuario, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     *
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Usuarios")
     */
    public function editUserAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetro a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($id);
            if (!$user || is_null($user)) {
                return $this->JsonResponseNotFound();
            }

            $name = $request->request->get('name', null);
            $rol = $request->request->get('rol', null);
            $genero = $request->request->get('genero', null);
            $fechaNacimiento = $request->request->get('fechaNacimiento');            
            $ruta = $request->request->get('ruta', null);
            $archivo = $request->request->get("archivo", null);
            $nombreArchivo = $request->request->get("nombreArchivo", null);
            $estatus = $request->request->get('estatus', null);


            $modificar = false;
            $borrar_archivo = false;

            if (trim($name) && !is_null($name) && $user->getName() != $name) {
                $user->setName($name);
                $modificar = true;
            }

            if ($rol) {
                if (!($rol == "ROLE_USER" || $rol == "ROLE_ADMIN")) {
                    $response['value'] = $rol;
                    $response['message'] = "Valores permitidos del ROL: ROLE_USER, ROLE_ADMIN";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $user->setRoles($rol);
                    $modificar = true;
                }
            }

            if ($genero) {
                if (!($genero == "1" || $genero == "2")) {
                    $response['value'] = $genero;
                    $response['message'] = "Valores permitidos del género: 1: Femenino, 2: Masculino";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($user->getGenero() != intval($genero)) {
                        $user->setGenero(intval($genero));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($fechaNacimiento) && $fechaNacimiento != '') {
                $d = \DateTime::createFromFormat("Y-m-d", $fechaNacimiento);
                if (!($d && $d->format("Y-m-d") === $fechaNacimiento)) {
                    $response['value'] = $fechaNacimiento;
                    $response['message'] = "La fecha de nacimiento debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if (!$user->getFechaNacimiento()) {
                        $fechaNacimiento = new \DateTime($fechaNacimiento);
                        $user->setFechaNacimiento($fechaNacimiento);
                        $modificar = true;
                    } elseif ($user->getFechaNacimiento()->format('Y-m-d') != $fechaNacimiento) {
                        $fechaNacimiento = new \DateTime($fechaNacimiento);
                        $user->setFechaNacimiento($fechaNacimiento);
                        $modificar = true;
                    }
                }
            }    
            
            $dir_uploads = $this->getParameter('dir_uploads');

            // Se crea el subdirectorio para los archivos del usuario
            if (isset($archivo)) {                                            
                $dir = $dir_uploads . 'profile/' . $user->getId() . '/';
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir, 750, true);
                }

                //Obtengo la direccion del archivo de foto de usuario para borrarla luego
                if(!is_null($user->getFoto())) {
                    $dir_uploads_old = $this->getParameter('dir_uploads').$user->getFoto();
                    $borrar_archivo = true;
                }                
            } 

            $msg = "";
            if (!file_exists($dir_uploads) && !is_dir($dir_uploads)) {
                $msg .= " Sin embargo, el archivo no se almaceno. Asegúrese de que exista el siguiente directorio con permisos de lectura y escritura: " . $dir_uploads;
            } else {
                if (!is_null($archivo) && !is_null($nombreArchivo)) {                                        

                    $nuevo_nombre = $nombreArchivo;
                    $dir = $dir_uploads . 'profile/' . $user->getId() . '/';

                    $ifp = fopen($dir . $nuevo_nombre, 'wb');
                    $data = explode(',', $archivo);
                    
                    if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);

                        $user->setFoto('profile/' . $user->getId() . '/' . $nuevo_nombre);

                        $modificar = true;                        
                    }
                }
            }           

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $estatus = intval($estatus) == 0 ? false : true;
                    if ($user->getIsActive() != $estatus) {
                        $user->setIsActive($estatus);
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($user);
                $this->em->flush();

                if ($borrar_archivo && !is_dir($dir_uploads_old) && file_exists($dir_uploads_old)) {
                    unlink($dir_uploads_old);
                }

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $user->getFoto();
                $userImg = $this->getParameter('dir_uploads') . $user->getFoto();

                if ($user->getFoto() && file_exists($userImg)) {
                    $user->setFoto($uploads);
                } else {
                    $user->setFoto('');
                }

            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($user, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar en forma lógica un usuario dado el ID
     * @Rest\Delete("/usuarios/{id}", name="user_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El usuario fue eliminado exitosamente",
     *     @Model(type=User::class)
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
     *     description="El ID del usuario"
     * )
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Usuarios")
     */
    public function deleteUserAction(Request $request, $id)
    {
        try {
            $user = $this->getDoctrine()->getManager()->getRepository("App:User")->findUserActivo($id);

            if (!$user || is_null($user) || $this->isGranted('ROLE_SUPER_ADMIN')) {
                return $this->JsonResponseNotFound();
            }
            // Se desencripta la clave para obtener el ID_TOKEN.
            $secretkey = $this->getParameter('secretkey');
            $password = CryptoJSAES::decrypt($user->getPasswordEncrypted(), $secretkey);

            // Obtener el ID_TOKEN de Firebase actualizado
            $responseFB = $this->firebase->signIn($user->getEmail(), $password);
            if ($this->firebase->getStatus() != 200) {
                $response['value'] = $user->getEmail();
                $response['message'] = "Error accediendo a cuenta en firebase: " . $responseFB["error"]["message"];

                return $this->JsonResponseBadRequest($response);
            }

            // Borrar registro de Firebase
            $responseFB = $this->firebase->deleteAccount($responseFB['idToken']);
            if ($this->firebase->getStatus() != 200) {
                $response['value'] = $user->getEmail();
                $response['message'] = "Error eliminando cuenta en firebase: " . $responseFB["error"]["message"];

                return $this->JsonResponseBadRequest($response);
            }

            $fb = $this->em->getRepository("App:Firebase")->findOneByUser($user->getId());
            if ($fb) {
                $this->em->remove($fb);
                $this->em->flush();
            }

            $email = $user->getEmail();
            $username = $user->getUsername();

            $timestamp = date('Ymdhi-');
            $email = (strlen($email) > 242) ?  $timestamp . substr($email, -242, 242) : $timestamp . $email;
            $username = (strlen($username) > 242) ?  $timestamp . substr($username, -242, 242) : $timestamp . $username;

            $user->setUsername($username);
            $user->setEmail($email);
            // eliminación lógica
            $user->setFechaEliminado(new \DateTime('now'));
            $user->setEliminado(true);
            $user->setIsActive(false);

            $this->em->persist($user);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * Obtiene los Teléfonos de contacto del Cliente/Profesional de la ODS
     * @Rest\Get("/ordenesServicio/{id}/telefonos", name="user_telefonos_profesional")
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
     * @SWG\Tag(name="Usuarios")
     */
    public function getTelefonosUserAction(Request $request, $id)
    {
        try {
            $ODS = $this->em->getRepository("App:OrdenServicio")->find($id);
            if (!$ODS || is_null($ODS)) {
                return $this->JsonResponseNotFound();
            }

            $telsCliente = [];
            $telsProfesional = [];
            $sistemaTipo = $ODS->getServicio()->getSistemaTipo();

            // Profesional|Afiliado
            /*if ($ODS->getProfesional()) {
                $profesionalUserID = $ODS->getProfesional()->getUser()->getId();
                $dirrProfDefe = $this->em->getRepository("App:Direccion")->findOneBy([
                    'user'           => $profesionalUserID,
                    'sistemaTipo'    => $sistemaTipo,
                    'predeterminada' => true,
                    'eliminado'      => false
                ]);
                if ($dirrProfDefe) {
                    $telsProfesional['tel1'] = $dirrProfDefe->getTelefono() ? $dirrProfDefe->getTelefono() : "";
                    $telsProfesional['tel2'] = $dirrProfDefe->getTelefonoMovil() ? $dirrProfDefe->getTelefonoMovil() : "";
                }
            }*/

            // Cliente
            $userID = $ODS->getUser()->getId();
            $dirrClieDefe = $this->em->getRepository("App:Direccion")->findOneBy([
                'user'           => $userID,
                'sistemaTipo'    => $sistemaTipo,
                'predeterminada' => true,
                'eliminado'      => false
            ]);
            if ($dirrClieDefe) {
                $telsCliente['tel1'] = $dirrClieDefe->getTelefono() ? $dirrClieDefe->getTelefono() : "";
                $telsCliente['tel2'] = $dirrClieDefe->getTelefonoMovil() ? $dirrClieDefe->getTelefonoMovil() : "";
            }

            $telefonosODS = [
                'cliente' => $telsCliente,
                'profesional' => $telsProfesional,
            ];
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($telefonosODS);
    }
}
