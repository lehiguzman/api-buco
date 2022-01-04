<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Firebase;
use App\Entity\Profesional;
use App\Entity\User;
use App\Service\CryptoJSAES\CryptoJSAES;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProfesionalController
 *
 * @Route("/api/v1/profesionales")
 */
class ProfesionalController extends BaseAPIController
{
    /**
     * Lista los profesionales
     * @Rest\Get("", name="profesional_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los profesionales",
     *     @Model(type=Profesional::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los profesionales"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id, nombre, apellido o correo.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre, apellido o correo.")          
     *
     * @SWG\Tag(name="Profesionales")
     */
    public function getAllProfesionalAction(Request $request)
    {

        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre', 'apellido', 'correo'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionales($params);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getUser()->getFoto();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getUser()->getFoto();
                if ($entity->getUser()->getFoto() && file_exists($dir_uploads)) {
                    $entity->getUser()->setFoto($uploads);
                } else {
                    $entity->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista los Profesionales y sus datos por Servicio
     * @Rest\Get("/servicio/{id}", name="profesional_list_all_servicio")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los profesionales por servicio"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los profesionales"
     * )
     *
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getAllProfesionalServicioAction(Request $request, $id)
    {
        $data = [];

        try {
            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findByServicio($id);

            foreach ($records as $record) {

                $profesional_id = $record->getId();

                //Obtengo la distancia de cada profesional individualmente           
                $latitud = 8.99797;
                $longitud = -79.508096;

                $distancia = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalByDistancia($profesional_id, $latitud, $longitud);

                $calificacion = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalByCalificacion($profesional_id);

                $metodos_pago = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalMetodoPago")->findbyProfesional($profesional_id);

                $ordenes_servicio = $this->getDoctrine()->getManager()->getRepository("App:OrdenServicioProfesional")->findOrdenServicioProfesionalActivo($profesional_id);

                $tareas = $this->getDoctrine()->getManager()->getRepository("App:ProfesionalTarea")->findbyProfesionalService($profesional_id, $id);

                $portafolio = $this->getDoctrine()->getManager()->getRepository("App:ArchivoPortafolio")->findArchivoPortafolioByProfesional($profesional_id);

                foreach ($portafolio as $entity) {
                    $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getRuta();
                    $dir_uploads = $this->getParameter('dir_uploads') . $entity->getRuta();
                    if ($entity->getRuta() && file_exists($dir_uploads)) {
                        $entity->setRuta($uploads);
                    } else {
                        $entity->setRuta('');
                    }
                }

                $data[] = [
                    'profesional' => $record,
                    'distancia' => $distancia,
                    'calificacion' => $calificacion,
                    'metodos_pago' => $metodos_pago,
                    'ordenes_servicio' => $ordenes_servicio,
                    'tareas' => $tareas,
                    'portafolio' => $portafolio,
                ];
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($data);
    }

    /**
     * Lista los profesionales más populares
     * @Rest\Get("/populares", name="profesional_list_all_populares")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los profesionales por calificación"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los profesionales"
     * )
     *
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getAllProfesionalPopularesAction(Request $request)
    {

        try {
            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findByCalificacion();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista los profesionales más populares
     * @Rest\Get("/{id}/populares", name="profesional_list_all_populares")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene un profesional por calificación dado el id"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo el profesional"
     * )
     *
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getProfesionalPopularAction(Request $request, $id)
    {

        try {
            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalByCalificacion($id);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * agregar a un nuevo profesional
     * @Rest\Post("", name="profesional_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Profesional agregado exitosamente",
     *     @Model(type=Profesional::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo profesional"
     * )
     *
     * @SWG\Parameter(
     *     name="_email",
     *     in="body",
     *     type="string",
     *     description="Correo electrónico del profesional",
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
     *     name="genero",
     *     in="body",
     *     type="integer",
     *     description="Género del profesional, Valores permitidos: 1: Femenino, 2: Masculino",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaNacimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de nacimiento del profesional en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="foto",
     *     in="body",
     *     type="integer",
     *     description="Ruta del archivo foto del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="apellido",
     *     in="body",
     *     type="string",
     *     description="Apellido del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="identificacion",
     *     in="body",
     *     type="string",
     *     description="Identificación del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="nacionalidad",
     *     in="body",
     *     type="string",
     *     description="Nacionalidad del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="direccion",
     *     in="body",
     *     type="string",
     *     description="Dirección del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipoCuenta",
     *     in="body",
     *     type="int",
     *     description="Tipo de cuenta bancaria, Valores permitidos: 1: Corriente, 2: Ahorros.",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="banco",
     *     in="body",
     *     type="string",
     *     description="Nombre del banco de la cuenta bancaria",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="cuentaBancaria",
     *     in="body",
     *     type="string",
     *     description="Cuenta bancaria del profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="destrezaDetalle",
     *     in="body",
     *     type="string",
     *     description="Detalle de la destreza del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="servicioID",
     *     in="body",
     *     type="int",
     *     description="ID del servicio que presta el profesional",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="string",
     *     description="Latitud de ubicación del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="string",
     *     description="Longitud de ubicación del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="radioCobertura",
     *     in="body",
     *     type="float",
     *     description="Radio de cobertura que abarca el profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="aniosExperiencia",
     *     in="body",
     *     type="int",
     *     description="Años de experiencia como profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function addProfesionalAction(Request $request)
    {
        $em = $this->em;

        try {
            $email = $request->request->get('_email', null);
            $username = $request->request->get('_username', null);
            $password = $request->request->get('_password', null);
            $genero = $request->request->get("genero", 2);
            $fechaNacimiento = $request->request->get("fechaNacimiento", null);
            $foto = $request->request->get("foto", null);
            $nombre = $request->request->get("nombre", null);
            $apellido = $request->request->get("apellido", null);
            $identificacion = $request->request->get("identificacion", null);
            $nacionalidad = $request->request->get("nacionalidad", null);
            $direccion = $request->request->get("direccion", null);
            $tipoCuenta = $request->request->get("tipoCuenta", null);
            $banco = $request->request->get("banco", null);
            $cuentaBancaria = $request->request->get("cuentaBancaria", null);
            $destrezaDetalle = $request->request->get('destrezaDetalle', null);
            $servicioID = $request->request->get("servicioID", null);
            // $comisionID = $request->request->get("comisionID", null);
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);
            $radioCobertura = $request->request->get("radioCobertura", 5);
            $aniosExperiencia = $request->request->get("aniosExperiencia", 0);

            // Validaciones
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

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca un nombre";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($apellido) == false) {
                $response['value'] = $apellido;
                $response['message'] = "Por favor introduzca un apellido";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($identificacion) == false) {
                $response['value'] = $identificacion;
                $response['message'] = "Por favor introduzca una identificación";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($nacionalidad) == false) {
                $response['value'] = $nacionalidad;
                $response['message'] = "Por favor introduzca una nacionalidad";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($direccion) == false) {
                $response['value'] = $direccion;
                $response['message'] = "Por favor introduzca una dirección";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($tipoCuenta)) {
                $response['value'] = $tipoCuenta;
                $response['message'] = "Por favor introduzca el tipo de cuenta";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($tipoCuenta == "1" || $tipoCuenta == "2")) {
                    $response['value'] = $tipoCuenta;
                    $response['message'] = "Valores permitidos del tipo de cuenta: 1: Corriente, 2: Ahorros";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($banco) == false) {
                $response['value'] = $banco;
                $response['message'] = "Por favor introduzca el banco";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($cuentaBancaria) == false) {
                $response['value'] = $cuentaBancaria;
                $response['message'] = "Por favor introduzca la cuenta bancaria";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($servicioID)) {
                $response['value'] = $servicioID;
                $response['message'] = "Por favor introduzca el servicio";
                return $this->JsonResponseBadRequest($response);
            } else {
                $servicio = $em->getRepository("App:Servicio")->findServicioActivo($servicioID);
                if (!$servicio) {
                    $response['value'] = $servicioID;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            // if (is_null($comisionID)) {
            //     $response['value'] = $comisionID;
            //     $response['message'] = "Por favor introduzca el tipo de comisión";
            //     return $this->JsonResponseBadRequest($response);
            // } else {
            //     $comision = $em->getRepository("App:Comision")->findComisionActiva($comisionID);
            //     if (!$comision) {
            //         $response['value'] = $comisionID;
            //         $response['message'] = "Tipo de comisión no encontrada";
            //         return $this->JsonResponseBadRequest($response);
            //     }
            // }

            // Nuevo registro en User
            $user = new User();
            $user->setName($nombre . ' ' . $apellido);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPlainPassword($password);
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setPasswordEncrypted(CryptoJSAES::encrypt($password, $this->getParameter('secretkey')));
            $user->setRoles("ROLE_PROFESIONAL");
            $user->setGenero(intval($genero));
            $user->setFechaNacimiento($fechaNacimiento);
            $user->setFoto($foto);
            $user->setIsActive(true);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            // Estatus según la documentación a recaudar del servicio
            $stds = $this->em->getRepository("App:ServicioTipoDocumento")->findByServicio($servicio->getId());
            $estatus = $stds ? 0 : 1;

            // Nuevo registro en Profesional
            $profesional = new Profesional();
            $profesional->setNombre($nombre);
            $profesional->setApellido($apellido);
            $profesional->setIdentificacion($identificacion);
            $profesional->setNacionalidad($nacionalidad);
            $profesional->setDireccion($direccion);
            $profesional->setTipoCuenta(intval($tipoCuenta));
            $profesional->setBanco($banco);
            $profesional->setCuentaBancaria($cuentaBancaria);
            $profesional->setDestrezaDetalle($destrezaDetalle);
            $profesional->setEstatus($estatus);
            $profesional->setUser($user);
            $profesional->setServicio($servicio);
            // $profesional->setComision($comision);
            $profesional->setLatitud(floatval($latitud));
            $profesional->setLongitud(floatval($longitud));
            $radioCobertura .= ".0";    // Para garantizar que el dato sea float
            $radioCobertura = floatval($radioCobertura);
            $profesional->setRadioCobertura($radioCobertura);
            $profesional->setAniosExperiencia(intval($aniosExperiencia));

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($profesional);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($user);
            $em->persist($profesional);
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

        return $this->JsonResponseSuccess($profesional, 201);
    }

    /**
     * obtener la información de un profesional dado el ID
     * @Rest\Get("/{id}", name="profesional_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el profesional basado en parámetro ID.",
     *     @Model(type=Profesional::class)
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getProfesionalAction(Request $request, $id)
    {

        try {
            $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $profesional->getUser()->getFoto();
        $dir_uploads = $this->getParameter('dir_uploads') . $profesional->getUser()->getFoto();
        if ($profesional->getUser()->getFoto() && file_exists($dir_uploads)) {
            $profesional->getUser()->setFoto($uploads);
        } else {
            $profesional->getUser()->setFoto('');
        }

        return $this->JsonResponseSuccess($profesional);
    }

    /**
     * obtener la información de un profesional dado el ID
     * @Rest\Get("/usuarios/{id}", name="profesional_user_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el profesional basado en parámetro ID del usuario correspondiente.",
     *     @Model(type=Profesional::class)
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
     * @IsGranted("ROLE_PROFESIONAL")
     * @SWG\Tag(name="Profesionales")
     */
    public function getProfesionalUserAction(Request $request, $id)
    {

        try {
            $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalUserActivo($id);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $profesional->getUser()->getFoto();
        $dir_uploads = $this->getParameter('dir_uploads') . $profesional->getUser()->getFoto();
        if ($profesional->getUser()->getFoto() && file_exists($dir_uploads)) {
            $profesional->getUser()->setFoto($uploads);
        } else {
            $profesional->getUser()->setFoto('');
        }

        return $this->JsonResponseSuccess($profesional);
    }

    /**
     * actualizar la información de un profesional
     * @Rest\Put("/{id}", name="profesional_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del profesional fue actualizada satisfactoriamente.",
     *     @Model(type=Profesional::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del profesional."
     * )
     *
     * @SWG\Parameter(
     *     name="genero",
     *     in="body",
     *     type="integer",
     *     description="Género del profesional, Valores permitidos: 1: Femenino, 2: Masculino",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaNacimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de nacimiento del profesional en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="foto",
     *     in="body",
     *     type="integer",
     *     description="Ruta del archivo foto del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="apellido",
     *     in="body",
     *     type="string",
     *     description="Apellido del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="identificacion",
     *     in="body",
     *     type="string",
     *     description="Identificacion del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="nacionalidad",
     *     in="body",
     *     type="string",
     *     description="Nacionalidad del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="direccion",
     *     in="body",
     *     type="string",
     *     description="Dirección del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="tipoCuenta",
     *     in="body",
     *     type="int",
     *     description="Tipo de cuenta bancaria, Valores permitidos: 1: Corriente, 2: Ahorros.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="banco",
     *     in="body",
     *     type="string",
     *     description="Nombre del banco de la cuenta bancaria",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="cuentaBancaria",
     *     in="body",
     *     type="string",
     *     description="Cuenta bancaria del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="destrezaDetalle",
     *     in="body",
     *     type="string",
     *     description="Detalle de la destreza del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="servicioID",
     *     in="body",
     *     type="int",
     *     description="ID del servicio que presta el profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="string",
     *     description="Latitud de ubicación del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="string",
     *     description="Longitud de ubicación del profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="radioCobertura",
     *     in="body",
     *     type="float",
     *     description="Radio de cobertura que abarca el profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="aniosExperiencia",
     *     in="body",
     *     type="int",
     *     description="Años de experiencia como profesional",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function editProfesionalAction(Request $request, $id)
    {

        try {

            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }

            $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }

            $genero = $request->request->get("genero", null);
            $fechaNacimiento = $request->request->get("fechaNacimiento", null);
            $foto = $request->request->get("foto", null);
            $nombre = $request->request->get("nombre", null);
            $apellido = $request->request->get("apellido", null);
            $identificacion = $request->request->get("identificacion", null);
            $nacionalidad = $request->request->get("nacionalidad", null);
            $direccion = $request->request->get("direccion", null);
            $tipoCuenta = $request->request->get("tipoCuenta", null);
            $banco = $request->request->get("banco", null);
            $cuentaBancaria = $request->request->get("cuentaBancaria", null);
            $destrezaDetalle = $request->request->get("destrezaDetalle", null);
            $servicioID = $request->request->get("servicioID", null);
            // $comisionID = $request->request->get("comisionID", null);
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);
            $radioCobertura = $request->request->get("radioCobertura", null);
            $aniosExperiencia = $request->request->get("aniosExperiencia", null);

            $modificar = false;
            $borrar_archivo = false;
            $user = $profesional->getUser();

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

            if (!is_null($foto) && $foto != '' && $user->getFoto() != $foto) {
                $dir_uploads = $this->getParameter('dir_uploads') . $foto;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $foto;
                    $response['message'] = "Archivo no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $user->getFoto();
                    if (file_exists($dir_uploads)) {
                        $borrar_archivo = true;
                    }
                    $user->setFoto($foto);
                    $modificar = true;
                }
            }

            if (trim($nombre) && !is_null($nombre) && $profesional->getNombre() != $nombre) {
                $profesional->setNombre($nombre);
                $user->setName($nombre . " " . $profesional->getApellido());
                $modificar = true;
            }

            if (trim($apellido) && !is_null($apellido) && $profesional->getApellido() != $apellido) {
                $profesional->setApellido($apellido);
                $user->setName($profesional->getNombre() . " " . $apellido);
                $modificar = true;
            }

            if (trim($identificacion) && !is_null($identificacion) && $profesional->getIdentificacion() != $identificacion) {
                $profesional->setIdentificacion($identificacion);
                $modificar = true;
            }

            if (trim($nacionalidad) && !is_null($nacionalidad) && $profesional->getNacionalidad() != $nacionalidad) {
                $profesional->setNacionalidad($nacionalidad);
                $modificar = true;
            }

            if (trim($direccion) && !is_null($direccion) && $profesional->getDireccion() != $direccion) {
                $profesional->setDireccion($direccion);
                $modificar = true;
            }

            if (trim($tipoCuenta) && !is_null($tipoCuenta) && $profesional->getTipoCuenta() != $tipoCuenta) {
                if (!($tipoCuenta == "1" || $tipoCuenta == "2")) {
                    $response['value'] = $tipoCuenta;
                    $response['message'] = "Valores permitidos del tipo de cuenta: 1: Corriente, 2: Ahorros";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $profesional->setTipoCuenta(intval($tipoCuenta));
                    $modificar = true;
                }
            }

            if (trim($banco) && !is_null($banco) && $profesional->getBanco() != $banco) {
                $profesional->setBanco($banco);
                $modificar = true;
            }

            if (trim($cuentaBancaria) && !is_null($cuentaBancaria) && $profesional->getCuentaBancaria() != $cuentaBancaria) {
                $profesional->setCuentaBancaria($cuentaBancaria);
                $modificar = true;
            }

            if (trim($destrezaDetalle) && !is_null($destrezaDetalle) && $profesional->getDestrezaDetalle() != $destrezaDetalle) {
                $profesional->setDestrezaDetalle($destrezaDetalle);
                $modificar = true;
            }

            if (!is_null($servicioID) && !$profesional->getEstatus()) {
                $servicio = $this->em->getRepository("App:Servicio")->findServicioActivo($servicioID);
                if (!$servicio) {
                    $response['value'] = $servicioID;
                    $response['message'] = "Servicio no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {

                    if ($profesional->getServicio() != $servicio) {

                        // Se debe eliminar los registros de profesional_tarea que tenía asociado el profesional con el anterior servicio
                        $profesional_tarea = $this->em->getRepository("App:ProfesionalTarea")->findbyProfessional($profesional->getId());
                        foreach ($profesional_tarea as $pt) {
                            if ($pt->getTarea()->getServicio() == $profesional->getServicio()) {
                                $this->em->remove($pt);
                                $this->em->flush();
                            }
                        }

                        // Se actualiza el nuevo servicio al profesional
                        $profesional->setServicio($servicio);
                        $modificar = true;
                    }
                }
            }

            // if (!is_null($comisionID)) {
            //     $comision = $this->em->getRepository("App:Comision")->findComisionActiva($comisionID);
            //     if (!$comision) {
            //         $response['value'] = $comisionID;
            //         $response['message'] = "Tipo de comisión no encontrada";
            //         return $this->JsonResponseBadRequest($response);
            //     } else {
            //         if ($profesional->getComision()->getId() != $comision->getId()) {
            //             $profesional->setComision($comision);
            //             $modificar = true;
            //         }
            //     }
            // }

            if (trim($latitud) && !is_null($latitud) && $profesional->getLatitud() != floatval($latitud)) {
                $profesional->setLatitud(floatval($latitud));
                $modificar = true;
            }

            if (trim($longitud) && !is_null($longitud) && $profesional->getLongitud() != floatval($longitud)) {
                $profesional->setLongitud(floatval($longitud));
                $modificar = true;
            }

            if (trim($radioCobertura) && !is_null($radioCobertura) && $profesional->getRadioCobertura() != floatval($radioCobertura)) {
                $profesional->setRadioCobertura(floatval($radioCobertura));
                $modificar = true;
            }

            if (!is_null($aniosExperiencia) && $profesional->getAniosExperiencia() != intval($aniosExperiencia)) {
                $profesional->setAniosExperiencia(intval($aniosExperiencia));
                $modificar = true;
            }

            // Verificar datos de la Entidad
            /*$errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }*/

            $errors = $this->validator->validate($profesional);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $user->setUpdatedAt(new \DateTime('now'));
                $this->em->persist($user);
                $this->em->persist($profesional);
                $this->em->flush();

                if ($borrar_archivo) {
                    unlink($dir_uploads);
                }
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($profesional, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un profesional dado el ID
     * @Rest\Delete("/{id}", name="profesional_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El profesional fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Profesional")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Profesionales")
     */
    public function deleteProfesionalAction(Request $request, $id)
    {
        try {
            $profesional = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }

            // Se desencripta la clave para obtener el ID_TOKEN.
            $password = CryptoJSAES::decrypt($profesional->getUser()->getPasswordEncrypted(), $this->getParameter('secretkey'));

            // Obtener el ID_TOKEN de Firebase actualizado
            $responseFB = $this->firebase->signIn($profesional->getUser()->getEmail(), $password);
            if ($this->firebase->getStatus() != 200) {
                $firebase_error = isset($responseFB["error"]) ? $responseFB["error"]["message"] : $responseFB;
                $response['value'] = $profesional->getUser()->getEmail();
                $response['message'] = "Error accediendo a cuenta en firebase: " . $firebase_error;

                return $this->JsonResponseBadRequest($response);
            }

            // Borrar registro de Firebase
            $responseFB = $this->firebase->deleteAccount($responseFB['idToken']);
            if ($this->firebase->getStatus() != 200) {
                $firebase_error = isset($responseFB["error"]) ? $responseFB["error"]["message"] : $responseFB;
                $response['value'] = $profesional->getUser()->getEmail();
                $response['message'] = "Error eliminando cuenta en firebase: " . $firebase_error;

                return $this->JsonResponseBadRequest($response);
            }

            $fb = $this->em->getRepository("App:Firebase")->findOneByUser($profesional->getUser()->getId());
            if ($fb) {
                $this->em->remove($fb);
                $this->em->flush();
            }

            // eliminación lógica
            $profesional->setFechaEliminado(new \DateTime('now'));
            $profesional->setEliminado(true);
            $profesional->setEstatus(0);

            $email = $profesional->getUser()->getEmail();
            $username = $profesional->getUser()->getUsername();

            $timestamp = date('Ymdhi-');
            $email = (strlen($email) > 242) ?  $timestamp . substr($email, -242, 242) : $timestamp . $email;
            $username = (strlen($username) > 242) ?  $timestamp . substr($username, -242, 242) : $timestamp . $username;

            $profesional->getUser()->setUsername($username);
            $profesional->getUser()->setEmail($email);
            $profesional->getUser()->setFechaEliminado(new \DateTime('now'));
            $profesional->getUser()->setEliminado(true);
            $profesional->getUser()->setIsActive(false);
            $profesional->getUser()->setUpdatedAt(new \DateTime('now'));
            $profesional->setUpdatedAt(new \DateTime('now'));

            $this->em->persist($profesional->getUser());
            $this->em->persist($profesional);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * Lista los profesionales por rango dada la longitud y latitud
     * @Rest\Post("/cobertura", name="profesional_list_all_by_rango")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los profesionales por rango"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los profesionales"
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="string",
     *     description="Latitud de ubicación",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="string",
     *     description="Longitud de ubicación",
     *     schema={},
     *     required=true
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getAllProfesionalByRangoAction(Request $request)
    {
        try {
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);

            if (trim($latitud) == false) {
                $response['value'] = $latitud;
                $response['message'] = "Por favor introduzca la latitud";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($longitud) == false) {
                $response['value'] = $longitud;
                $response['message'] = "Por favor introduzca la longitud";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findbyDistancia(floatval($latitud), floatval($longitud));
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener un profesional por rango dada la longitud, latitud y el id del profesional
     * @Rest\Post("/{id}/cobertura", name="profesional_list_by_rango")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene un profesional por rango"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo el profesional"
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="string",
     *     description="Latitud de ubicación",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="string",
     *     description="Longitud de ubicación",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="ID del Profesional"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getProfesionalByRangoAction(Request $request, $id)
    {
        try {
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);

            if (trim($latitud) == false) {
                $response['value'] = $latitud;
                $response['message'] = "Por favor introduzca la latitud";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($longitud) == false) {
                $response['value'] = $longitud;
                $response['message'] = "Por favor introduzca la longitud";
                return $this->JsonResponseBadRequest($response);
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalByDistancia($id, floatval($latitud), floatval($longitud));
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista los documentos de un profesional
     * @Rest\Get("/{id}/documentos", name="profesional_list_documento")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los documentos por profesional",
     *     @Model(type=Documento::class)
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="ID del Profesional"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Profesionales")
     */
    public function getAllDocumentoByProfesionalAction(Request $request, $id)
    {
        try {
            $profesional = $this->em->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesional || is_null($profesional)) {
                return $this->JsonResponseNotFound();
            }

            /*$entities = $this->em->getRepository("App:Documento")->findBy([
                'profesional' => $profesional->getId(),
                'eliminado' => 0
            ]);*/

            $entities = $this->em->getRepository("App:Documento")->findActiveDocsByProfesional($id);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entities);
    }
}
