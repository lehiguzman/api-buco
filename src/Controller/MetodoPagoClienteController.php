<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\MetodoPagoCliente;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// CryptoJSAES
use App\Service\CryptoJSAES\CryptoJSAES;
// Servicio PayUs
use App\Service\PayUsServicio;

/**
 * Class MetodoPagoClienteController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class MetodoPagoClienteController extends BaseAPIController
{
    /**
     * Lista los metodos de pago de un cliente
     * @Rest\Get("/metodosPagoCliente", name="metodosPagoCliente_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los Metodos de pago del cliente",
     *     @Model(type=MetodoPagoCliente::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los Metodos de pago del cliente"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre")
     *
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     */
    public function getAllMetodoPagoClienteAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:MetodoPagoCliente")->findMetodosPagoCliente($params);

            foreach ($records as $metodoPago) {
                $secretKey = $this->getParameter('secretkey');
                $metodoPago->setNumeroTarjeta($this->decryptTDC($secretKey, $metodoPago->getNumeroTarjeta(), 'numeroTarjeta'));
                $metodoPago->setCvv($this->decryptTDC($secretKey, $metodoPago->getCvv(), 'cvv'));
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * @Rest\Post("/metodosPagoCliente", name="metodosPagoCliente_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Método de pago agregado exitosamente.",
     *     @Model(type=MetodoPagoCliente::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo Método de Pago de Cliente"
     * )
     *
     * @SWG\Parameter(
     *     name="userId",
     *     in="body",
     *     type="integer",
     *     description="Id del usuario cliente que pertenece el método de pago",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="numeroTarjeta",
     *     in="body",
     *     type="string",
     *     description="Número de la tarjeta",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Parameter(
     *     name="mesAnioExpiracion",
     *     in="body",
     *     type="string",
     *     description="Mes y año de expiración de la tarjeta en formato MM/AA",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="cvv",
     *     in="body",
     *     type="string",
     *     description="Código CVV de la tarjeta",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del cliente en la tarjeta",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="integer",
     *     description="Estatus del metodo de pago del cliente. Valores permitidos: 0: Inactivo, 1: Activo.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="token",
     *     in="body",
     *     type="string",
     *     description="Token recibido de la pasarela de pago",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="tokenPrueba",
     *     in="body",
     *     type="string",
     *     description="Token para confirmar",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     */
    public function addMetodoPagoClienteAction(Request $request, PayUsServicio $payusServ)
    {
        $em = $this->em;

        try {
            $userId = $request->request->get("userId");
            $numeroTarjeta = $request->request->get("numeroTarjeta");
            $mesAnioExpiracion = $request->request->get("mesAnioExpiracion");
            $cvv = $request->request->get("cvv");
            $nombre = $request->request->get("nombre");
            $status = $request->request->get("status", 1);
            $token = $request->request->get("token", null);

            $secretKey = $this->getParameter('secretkey');

            if (is_null($userId)) {
                $response['value'] = $userId;
                $response['message'] = "Por favor introduzca el cliente usuario";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $em->getRepository("App:User")->findUserActivo($userId);
                if (!$user) {
                    $response['value'] = $userId;
                    $response['message'] = "Usuario no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($numeroTarjeta) == false) {
                $response['value'] = $numeroTarjeta;
                $response['message'] = "Por favor introduzca número de tarjeta";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!is_numeric($numeroTarjeta)) {
                    $response['value'] = $numeroTarjeta;
                    $response['message'] = "El número de tarjeta debe ser numérico";
                    return $this->JsonResponseBadRequest($response);
                }

                if (strlen($numeroTarjeta) < 14 || strlen($numeroTarjeta) > 16) {
                    $response['value'] = $numeroTarjeta;
                    $response['message'] = "El número de tarjeta debe estar entre 14 y 16 dígitos";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $mpcs = $em->getRepository("App:MetodoPagoCliente")->findMetodoPagoClienteActivo($userId);
            foreach ($mpcs as $mpc) {
                $nt = CryptoJSAES::decrypt($mpc->getNumeroTarjeta(), $secretKey);
                if ($nt == $numeroTarjeta) {
                    $response['value'] = $numeroTarjeta;
                    $response['message'] = "Número de tarjeta repetida para este cliente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($mesAnioExpiracion) == false) {
                $response['value'] = $mesAnioExpiracion;
                $response['message'] = "Por favor introduzca el mes y año de expiración";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (strlen($mesAnioExpiracion) != 5) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Formato del mes y año de expiración debe ser MM/AA";
                    return $this->JsonResponseBadRequest($response);
                }

                if (substr_count($mesAnioExpiracion, '/') != 1) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Formato del mes y año de expiración debe ser MM/AA";
                    return $this->JsonResponseBadRequest($response);
                }

                $date_arr = explode("/", $mesAnioExpiracion);
                $mes = intval($date_arr[0]);
                $anio = intval($date_arr[1]);

                if ($mes < 1 || $mes > 12) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Mes de expiración es incorrecto";
                    return $this->JsonResponseBadRequest($response);
                }

                $anio_mes_actual = date('y-m');
                $anio_mes = $anio . '-' . $mes;

                if ($anio_mes < $anio_mes_actual) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Tarjeta vencida";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($cvv) == false) {
                $response['value'] = $cvv;
                $response['message'] = "Por favor introduzca número de cvv";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del cliente";
                return $this->JsonResponseBadRequest($response);
            }

            if (!is_null($status)) {
                if (!($status == "0" || $status == "1")) {
                    $response['value'] = $status;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $metodoPago = new MetodoPagoCliente();
            $metodoPago->setUser($user);
            $metodoPago->setNumeroTarjeta(CryptoJSAES::encrypt($numeroTarjeta, $secretKey)); // OJO: Se guardará el dato que provenga de la pasarela de pago
            $metodoPago->setMesAnioExpiracion($mesAnioExpiracion);
            $metodoPago->setCvv($cvv); // OJO: Se guardará el dato que provenga de la pasarela de pago
            $metodoPago->setNombre($nombre);
            $metodoPago->setStatus(intval($status));
            $metodoPago->setToken($token); // OJO: Se guardará el dato que provenga de la pasarela de pago
            $metodoPago->setCreatedAt(new \DateTime('now'));
            $metodoPago->setUpdatedAt(new \DateTime('now'));

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($metodoPago);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $datos = [
                'cliente' => $metodoPago->getUser()->getId(),
                'numero' => $numeroTarjeta,
                'mesanio' => str_replace("/", "", $mesAnioExpiracion),
                'cvv' => $cvv,
            ];
            $resp = $payusServ->registrarTarjeta($datos);
            if (isset($resp) && $resp['token'] && $resp['code'] == 200) {
                $metodoPago->setToken($resp['token']);
            } elseif (isset($resp) && $resp['error']) {
                $resp['value'] = null;
                return $this->JsonResponseError($resp, 'check_parameters');
            } else {
                $response['message'] = "No se puedo a generar el token de la tarjeta";
                return $this->JsonResponseBadRequest($response);
            }

            $em->persist($metodoPago);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $metodoPago->setNumeroTarjeta($this->decryptTDC($secretKey, $metodoPago->getNumeroTarjeta(), 'numeroTarjeta'));
        $metodoPago->setCvv($this->decryptTDC($secretKey, $metodoPago->getCvv(), 'cvv'));

        return $this->JsonResponseSuccess($metodoPago, 201);
    }

    /**
     * obtener la información del método de pago dado el ID
     * @Rest\Get("/metodosPagoCliente/{id}", name="metodosPagoCliente_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el métodos de pago del cliente por el id",
     *     @Model(type=MetodoPagoCliente::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="El método de pago por cliente no existe."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo el método de pago"
     * )
     * 
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del metodo de pago"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     */
    public function getMetodoPagoClienteAction(Request $request, $id)
    {
        try {
            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPagoCliente")->findMetodoPagoActivo($id);

            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }

            $secretKey = $this->getParameter('secretkey');
            $metodoPago->setNumeroTarjeta($this->decryptTDC($secretKey, $metodoPago->getNumeroTarjeta(), 'numeroTarjeta'));
            $metodoPago->setCvv($this->decryptTDC($secretKey, $metodoPago->getCvv(), 'cvv'));
        } catch (Exception $ex) {

            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPago);
    }

    /**
     * obtener la información de metodos de pago de cliente dado el ID del cliente
     * @Rest\Get("/metodosPagoClienteUser/{userId}", name="metodosPagoClienteUser_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene los métodos de pago por cliente basado en el Id del usuario.",
     *     @Model(type=MetodoPagoCliente::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="No existen métodos de pago para este usuario"
     * )
     * 
     * * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *     
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="string",
     *     description="Id del cliente"
     * )
     * 
     * @SWG\Parameter(
     *     name="token",
     *     in="body",
     *     type="string",
     *     description="Nombre del cliente en la tarjeta",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     */
    public function getMetodoPagoClienteUserAction(Request $request, $userId)
    {
        try {
            $metodoPagoCliente = $this->getDoctrine()->getManager()->getRepository("App:MetodoPagoCliente")->findMetodoPagoClienteActivo($userId);

            /*if (!$metodoPagoCliente || is_null($metodoPagoCliente)) {
                return $this->JsonResponseNotFound();
            }*/

            foreach ($metodoPagoCliente as $metodoPago) {
                $secretKey = $this->getParameter('secretkey');
                $metodoPago->setNumeroTarjeta($this->decryptTDC($secretKey, $metodoPago->getNumeroTarjeta(), 'numeroTarjeta'));
                $metodoPago->setCvv($this->decryptTDC($secretKey, $metodoPago->getCvv(), 'cvv'));
            }
        } catch (Exception $ex) {

            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($metodoPagoCliente);
    }

    /**
     * actualizar la información del metodo de pago de un cliente
     * @Rest\Put("/metodosPagoCliente/{id}", name="metodosPagoCliente_edit")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="La información de método de pago del cliente fue actualizada exitosamente.",
     *     @Model(type=MetodoPagoCliente::class)
     * )
     *
     * * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del método de pago del cliente."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del método de pago del cliente"
     * )
     *
     * @SWG\Parameter(
     *     name="mesAnioExpiracion",
     *     in="body",
     *     type="string",
     *     description="Mes y año de expiración de la tarjeta en formato MM/AA",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="cvv",
     *     in="body",
     *     type="string",
     *     description="Código CVV de la tarjeta",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del cliente en la tarjeta",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="integer",
     *     description="Estatus del metodo de pago del cliente. Valores permitidos: 0: Inactivo, 1: Activo.",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     */
    public function editMetodosPagoClienteAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPagoCliente")->findMetodoPagoActivo($id);
            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }

            $mesAnioExpiracion = $request->request->get("mesAnioExpiracion");
            $cvv = $request->request->get("cvv");
            $nombre = $request->request->get("nombre");
            $status = $request->request->get("status", 1);

            $secretKey = $this->getParameter('secretkey');
            $modificar = false;

            if (trim($mesAnioExpiracion) && !is_null($mesAnioExpiracion) && $metodoPago->getMesAnioExpiracion() != $mesAnioExpiracion) {
                if (strlen($mesAnioExpiracion) != 5) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Formato del mes y año de expiración debe ser MM/AA";
                    return $this->JsonResponseBadRequest($response);
                } elseif (substr_count($mesAnioExpiracion, '/') != 1) {
                    $response['value'] = $mesAnioExpiracion;
                    $response['message'] = "Formato del mes y año de expiración debe ser MM/AA";
                    return $this->JsonResponseBadRequest($response);
                } else {

                    $date_arr = explode("/", $mesAnioExpiracion);
                    $mes = intval($date_arr[0]);
                    $anio = intval($date_arr[1]);

                    if ($mes < 1 || $mes > 12) {
                        $response['value'] = $mesAnioExpiracion;
                        $response['message'] = "Mes de expiración es incorrecto";
                        return $this->JsonResponseBadRequest($response);
                    } else {

                        $anio_mes_actual = date('y-m');
                        $anio_mes = $anio . '-' . $mes;

                        if ($anio_mes < $anio_mes_actual) {
                            $response['value'] = $mesAnioExpiracion;
                            $response['message'] = "Tarjeta vencida";
                            return $this->JsonResponseBadRequest($response);
                        } else {
                            $metodoPago->setMesAnioExpiracion($mesAnioExpiracion);
                            $modificar = true;
                        }
                    }
                }
            }

            if (trim($cvv) && !is_null($cvv) && $metodoPago->getCvv() != $cvv) {
                $metodoPago->setCvv($cvv);
                $modificar = true;
            }

            if (trim($nombre) && !is_null($nombre) && $metodoPago->getNombre() != $nombre) {
                $metodoPago->setNombre($nombre);
                $modificar = true;
            }

            if (!is_null($status)) {
                if (!($status == "0" || $status == "1")) {
                    $response['value'] = $status;
                    $response['message'] = "Valores permitidos del status: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $status = intval($status) == 0 ? false : true;
                    if ($metodoPago->getStatus() != $status) {
                        $metodoPago->setStatus(intval($status));
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($metodoPago);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $metodoPago->setUpdatedAt(new \DateTime('now'));
                $this->em->persist($metodoPago);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $metodoPago->setNumeroTarjeta($this->decryptTDC($secretKey, $metodoPago->getNumeroTarjeta(), 'numeroTarjeta'));
        $metodoPago->setCvv($this->decryptTDC($secretKey, $metodoPago->getCvv(), 'cvv'));

        return $this->JsonResponseSuccess($metodoPago, 200, "¡Registro modificado con éxito...!");
    }

    /**
     * eliminar un método de pago dado el ID
     * @Rest\Delete("/metodosPagoCliente/{id}", name="metodosPagoCliente_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El método de pago fue eliminado exitosamente"
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
     *     description="Id del método de pago del cliente"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Métodos de Pago | Clientes")
     * 
     */
    public function deleteMetodosPagoClienteAction(Request $request, $id)
    {
        try {
            $metodoPago = $this->getDoctrine()->getManager()->getRepository("App:MetodoPagoCliente")->findMetodoPagoActivo($id);

            if (!$metodoPago || is_null($metodoPago)) {
                return $this->JsonResponseNotFound();
            }

            // eliminación lógica
            $metodoPago->setFechaEliminado(new \DateTime('now'));
            $metodoPago->setEliminado(true);
            $metodoPago->setStatus(0);
            $metodoPago->setUpdatedAt(new \DateTime('now'));

            $this->em->persist($metodoPago);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    public function decryptTDC($secretKey, $data, $field)
    {
        switch ($field) {
            case 'numeroTarjeta':
                $numeroTarjeta = CryptoJSAES::decrypt($data, $secretKey);
                $decrypted = substr($numeroTarjeta, 0, 6) . '******' . substr($numeroTarjeta, -4);
                break;

            case 'cvv':
                $cvv = $data;
                $decrypted = '***';
                break;

            default:
                $decrypted = $data;
        }

        return $decrypted;
    }
}
