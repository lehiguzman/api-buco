<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ClienteTarjetas;
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
 * ClienteTarjetas Controller
 *
 * @Route("/api/v1/clientes/tarjetas")
 * @IsGranted({"ROLE_CLIENTE", "ROLE_USER"})
 */
class ClienteTarjetasController extends BaseAPIController
{
    /**
     * Lista todas las tarjetas del Cliente.
     * @Rest\Get("", name="clientetarjeta_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todas las tarjetas del Cliente.",
     *     @Model(type=ClienteTarjetas::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     *
     * @SWG\Tag(name="Clientes Tarjetas")
     */
    public function getAllClienteTarjetaAction()
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            return $this->JsonResponseAccessDenied();
        }

        try {
            $records = $this->em->getRepository("App:ClienteTarjetas")->findBy([
                'cliente' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            foreach ($records as $record) {
                $record->setCvv("***");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Registra una Tarjeta del Cliente.
     * @Rest\Post("", name="clientetarjeta_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Tarjeta registrada satisfactoriamente.",
     *     @Model(type=ClienteTarjetas::class)
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
     *     required=true,
     *     description="Parámetros para registrar una nueva tarjeta. fechaExpiracion: MM/AA",
     *     @SWG\Schema(
     *         @SWG\Property(property="nombre", type="string"),
     *         @SWG\Property(property="numero", type="string"),
     *         @SWG\Property(property="fechaExpiracion", type="string"),
     *         @SWG\Property(property="cvv", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Clientes Tarjetas")
     */
    public function addClienteTarjetaAction(Request $request, PayUsServicio $payusServ)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            return $this->JsonResponseAccessDenied();
        }

        try {
            $Cliente = $this->em->getRepository("App:User")->findOneBy([
                'id' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Cliente) {
                $response['value'] = $this->getUser()->getEmail();
                $response['message'] = "Usuario no encontrado";
                return $this->JsonResponseBadRequest($response);
            }

            $nombre = $request->request->get("nombre", null);
            $numero = $request->request->get("numero", null);
            $fechaExpiracion = $request->request->get("fechaExpiracion", null);
            $cvv = $request->request->get("cvv", null);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['info'] = "Por favor introduzca parámetro nombre";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($numero) == false) {
                $response['value'] = $numero;
                $response['info'] = "Por favor introduzca parámetro numero";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (!is_numeric($numero)) {
                    $response['value'] = $numero;
                    $response['message'] = "El número de tarjeta debe ser numérico";
                    return $this->JsonResponseBadRequest($response);
                }

                if (strlen($numero) < 14 || strlen($numero) > 16) {
                    $response['value'] = $numero;
                    $response['message'] = "El número de tarjeta debe estar entre 14 y 16 dígitos";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($fechaExpiracion) == false) {
                $response['value'] = $fechaExpiracion;
                $response['info'] = "Por favor introduzca parámetro fechaExpiracion";
                return $this->JsonResponseBadRequest($response);
            } else {
                if (strlen($fechaExpiracion) != 5 || substr_count($fechaExpiracion, '/') != 1) {
                    $response['value'] = $fechaExpiracion;
                    $response['message'] = "Formato del mes y año de expiración debe ser MM/AA";
                    return $this->JsonResponseBadRequest($response);
                }

                $date_arr = explode("/", $fechaExpiracion);
                $mes = intval($date_arr[0]);
                $anio = intval($date_arr[1]);
                if ($mes < 1 || $mes > 12) {
                    $response['value'] = $fechaExpiracion;
                    $response['message'] = "Mes de expiración es incorrecto";
                    return $this->JsonResponseBadRequest($response);
                }

                $anio_mes = $anio . '-' . $mes;
                if ($anio_mes < date('y-m')) {
                    $response['value'] = $fechaExpiracion;
                    $response['message'] = "Tarjeta vencida";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($cvv) == false) {
                $response['value'] = $cvv;
                $response['info'] = "Por favor introduzca parámetro cvv";
                return $this->JsonResponseBadRequest($response);
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $Tarjeta = new ClienteTarjetas();
            $Tarjeta->setCliente($this->getUser());
            $Tarjeta->setNombre($nombre);
            $Tarjeta->setNumero("XXXX-XXXX-XXXX-" . substr($numero, -4));
            $Tarjeta->setFechaExpiracion($fechaExpiracion);
            $Tarjeta->setCvv(CryptoJSAES::encrypt($cvv, $this->getParameter('secretkey')));

            /* >>>========== Servicio de PayUs ========== */
            $datos = [
                'cliente' => $this->getUser()->getId(),
                'numero' => $numero,
                'mesanio' => str_replace("/", "", $fechaExpiracion),
                'cvv' => $cvv,
            ];
            $resp = $payusServ->registrarTarjeta($datos);
            // return $this->JsonResponse($resp);
            if (isset($resp) && isset($resp['token']) && (isset($resp['code']) && $resp['code'] == 200)) {
                $Tarjeta->setTokenPayus($resp['token']);
            } elseif (isset($resp) && isset($resp['error'])) {
                $resp['value'] = null;
                return $this->JsonResponseBadRequest($resp);
            } else {
                $response['message'] = "No se puedo a generar el token de la tarjeta";
                return $this->JsonResponseBadRequest($response);
            }
            /* <<<========== Servicio de PayUs ========== */

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($Tarjeta);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $this->em->persist($Tarjeta);
            $this->em->flush();

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        $Tarjeta->setCvv("***");

        return $this->JsonResponseSuccess($Tarjeta, 201);
    }

    /**
     * Obtiene la Tarjeta del Cliente.
     * @Rest\Get("/{id}", name="clientetarjeta_details")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la Tarjeta del Cliente.",
     *     @Model(type=ClienteTarjetas::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador de la Tarjeta")
     *
     *
     * @SWG\Tag(name="Clientes Tarjetas")
     */
    public function getClienteTarjetaAction($id)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            return $this->JsonResponseAccessDenied();
        }

        try {
            $Tarjeta = $this->em->getRepository("App:ClienteTarjetas")->findOneBy([
                'id' => $id,
                'cliente' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Tarjeta || is_null($Tarjeta)) {
                return $this->JsonResponseNotFound();
            }
            $Tarjeta->setCvv("***");
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($Tarjeta);
    }

    /**
     * Actualiza el nombre de la Tarjeta.
     * @Rest\Put("/{id}", name="clientetarjeta_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Registro actualizado satisfactoriamente.",
     *     @Model(type=ClienteTarjetas::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ClienteTarjetas")
     * 
     * @SWG\Parameter(
     *     name="Parámetros",
     *     in="body",
     *     required=true,
     *     description="Nombre de la Tarjeta.",
     *     @SWG\Schema(
     *         @SWG\Property(property="nombre", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Clientes Tarjetas")
     */
    public function editClienteTarjetaAction(Request $request, $id)
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

            $Tarjeta = $this->em->getRepository("App:ClienteTarjetas")->findOneBy([
                'id' => $id,
                'cliente' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Tarjeta || is_null($Tarjeta)) {
                return $this->JsonResponseNotFound();
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $nombre = $request->request->get("nombre", null);

            $modificar = false;
            if (trim($nombre) && !is_null($nombre) && $Tarjeta->getNombre() != $nombre) {
                $Tarjeta->setNombre($nombre);
                $modificar = true;
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($Tarjeta);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($Tarjeta);
                $this->em->flush();

                // Confirma Transacciones a Base de Datos
                $this->em->getConnection()->commit();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($Tarjeta, 200, "¡Registro modificado con éxito!");
    }

    /**
     * Elimina un ClienteTarjetas.
     * @Rest\Delete("/{id}", name="clientetarjeta_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="ClienteTarjetas eliminado satisfactoriamente.",
     *     @Model(type=ClienteTarjetas::class)
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error en solicitud."
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del ClienteTarjetas")
     *
     *
     * @SWG\Tag(name="Clientes Tarjetas")
     */
    public function deleteClienteTarjetaAction($id)
    {
        if ($this->isGranted('ROLE_CALLCENTER')) {
            return $this->JsonResponseAccessDenied();
        }

        try {
            $Tarjeta = $this->em->getRepository("App:ClienteTarjetas")->findOneBy([
                'id' => $id,
                'cliente' => $this->getUser()->getId(),
                'eliminado' => 0
            ]);
            if (!$Tarjeta || is_null($Tarjeta)) {
                return $this->JsonResponseNotFound();
            }

            // eliminación lógica
            $Tarjeta->setFechaEliminado(new \DateTime('now'));
            $Tarjeta->setEliminado(true);

            $this->em->persist($Tarjeta);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($Tarjeta, 200, "¡Registro eliminado con éxito!");
    }
}
