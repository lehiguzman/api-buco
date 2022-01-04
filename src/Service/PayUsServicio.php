<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Unirest as UR;

/**
 * PayUs Servicio (Pasarela de Pagos)
 *
 * Conexión con REST API de PayUs
 *
 * Referencias:
 * https://apipasarela.strappinc.net
 *
 * @author Strapp International Inc.
 */
class PayUsServicio
{
    private $urlApi;
    private $guidComercio;
    private $guidCuenta;
    private $apikey;
    private $username;
    private $password;
    private $serializer;

    // --- endpoints ---
    private $endpointLogin         = "/api/login_check";
    private $endpointTransAutoConf = "/v1/transactions/autoconf";
    private $endpointCardStore     = "/v1/card/store";

    private $bodyPago = [
        'guid_user'        => null,         //GUID Único por Usuario del Portal de Pagos - Paŕametro encontrado en .env
        'guid_cuenta'      => null,         //GUID Único por Cuenta del Portal de Pagos - Parámetro encontrado en .env
        'order_id'         => '1234567890', //Número de Orden a pagar / Controlado por nosotros ID de la orden de servicio
        'installments'     => '1',          //Valor Fijo indica que el pago será realizado en su totalidad es decir "1 Cuota"
        'installment_type' => '4',          //Valor Fijo indica si el pago se hará en cuotas (4 significa que no)
        // 'authorizer_id'         => null,        //Tipo de tarjeta (1:Visa;2:MasterCard)
        'amount'           => null,         //Monto sin decimales, los decimales deben representarse en los ultimos dos dígitos (100,25$ = 10025)
        // 'identification_number' => 'V',
        // 'card_number'           => null,        //Número de Tarjeta (A reemplazar por Token)
        // 'card_expiry_date'      => null,        //Fecha de expiración de Tarjeta (A reemplazar por Token)
        // 'card_security_code'    => null,        //Codigo de seguridad de Tarjeta (A reemplazar por Token)
        // 'card_stored'           => 0,           //Boolean - Indica si se utilizará una tarjeta almacenada o no
        'card_token'       => null,         //String - token de la TDC
        'migrado'          => false,        //Valor fijo - Indica si el registro se ha migrado/realizado en el Portal de Pagos directamente

        // 'merchant_usn'          => null,        //Número identificador para el MerchantUsn No relevante - puede ser fijo
        // 'apikey'                => null,
    ];

    private $bodyTarjeta = [
        'guid_user'        => null,        //GUID Único por Usuario del Portal de Pagos - Paŕametro encontrado en .env
        'guid_cuenta'      => null,        //GUID Único por Cuenta del Portal de Pagos - Parámetro encontrado en .env
        'merchant_usn'     => null,        //Número identificador para el MerchantUsn No relevante - puede ser fijo
        'customer_id'      => null,        //ID Cliente de AzLogistic
        'authorizer_id'    => null,        //Tipo de tarjeta (1:Visa;2:MasterCard)
        'card_number'      => null,        //Número de Tarjeta
        'card_expiry_date' => null,        //Fecha de expiración de Tarjeta
        // 'apikey'            => null         //ApiKey Único por Usuario
    ];

    public function __construct(
        EntityManagerInterface $entityManager,
        $urlApi,
        $guidComercio,
        $guidCuenta,
        $apikey,
        $username,
        $passwordPlain,
        SerializerInterface $serializer
    ) {
        $this->em = $entityManager;
        $this->urlApi = $urlApi;
        $this->guidComercio = $guidComercio;
        $this->guidCuenta = $guidCuenta;
        $this->apikey = $apikey;
        $this->username = $username;
        $this->password = $passwordPlain;
        $this->serializer = $serializer;
    }

    /**
     * Loguearse en la PayUs
     * 
     * @return Array [code, token, message]
     */
    private function login()
    {
        $headers = array(
            'Content-Type' => 'application/json',
            'apikey'       => $this->apikey
        );
        $bodyAuth = array(
            '_username' => $this->username,
            '_password' => $this->password
        );
        $code = null;
        $token = null;
        $message = null;

        $jsonBodyAuth = $this->serializer->serialize($bodyAuth, "json");
        $urlAuth = $this->urlApi . $this->endpointLogin;
        $responseAuth = UR\Request::post($urlAuth, $headers, $jsonBodyAuth);

        // return $responseAuth;
        if (isset($responseAuth) && isset($responseAuth->code)) {
            $code = $responseAuth->code;
            $responseJson = null;
            if (isset($responseAuth->raw_body)) {
                $responseJson = json_decode($responseAuth->raw_body, true);
            }
            if ($responseAuth->code == 200) {
                $message = "success";
                $token = $responseJson['token'] ?? null;
            } else { // error en solicitud
                $message = $responseJson['message'] ?? null;
            }
        } else { // sin respuesta
            $message = "error request";
        }

        return [
            'code' => $code,
            'token' => $token,
            'message' => $message
        ];
    }

    /**
     * Procesa el pago a través de Plataforma PayUs
     */
    public function procesarPago($datos = null)
    {
        if (is_null($datos) || !isset($datos['orden']) || !isset($datos['montoTotal']) || !isset($datos['cardToken'])) {
            return [
                'message' => "No se enviaron los parámetros para esta transacción."
            ];
        }

        $respLogin = $this->login();
        if ($respLogin['code'] == 200 && $respLogin['token']) {
            $headers = array(
                'Content-Type'  => 'application/json',
                'apikey'        => $this->apikey,
                'Authorization' => 'Bearer ' . $respLogin['token']
            );
            $bodyTrans = $this->bodyPago;
            $bodyTrans['guid_user'] = $this->guidComercio;
            $bodyTrans['guid_cuenta'] = $this->guidCuenta;
            $bodyTrans['order_id'] = $datos['orden'] . time();
            $bodyTrans['card_token'] = $datos['cardToken'] ?? null;

            // formatear monto total
            $montoTotal = money_format("%.2n", $datos['montoTotal']);
            $montoTotal = str_replace(".", "", $montoTotal);
            $montoTotal = str_replace(",", "", $montoTotal);
            $bodyTrans['amount'] = $montoTotal;

            $code = null;
            $error = true;
            $message = null;
            $response = null;
            $confirmed = false;
            $responseJson = null;

            $jsonBodyTrans = $this->serializer->serialize($bodyTrans, "json");
            $urlTransAutoConf = $this->urlApi . $this->endpointTransAutoConf;
            $responseAutoConf = UR\Request::post($urlTransAutoConf, $headers, $jsonBodyTrans);

            // return $responseAutoConf;
            if (isset($responseAutoConf) && isset($responseAutoConf->code)) {
                $code = $responseAutoConf->code;
                if (isset($responseAutoConf->raw_body)) {
                    $responseJson = json_decode($responseAutoConf->raw_body, true);
                    $code = $responseJson['code'] ?? null;
                    if (isset($responseJson['data'])) {
                        $response = json_decode($responseJson['data'], true);
                    }
                }
                $message = "Payment not processed.";
                if ($code == 200 && intval($response['code']) == 0) {
                    $message = "OK. Transaction successful.";
                    $error = false;

                    if ($response['payment']['status'] == "CON") {
                        $confirmed = true;
                        $message .= " Transaction confirmed by the financial institution.";
                    } elseif ($response['payment']['status'] == "NOV") {
                        $message .= " Transaction just created. Not confirmed.";
                    }
                } else { // error en solicitud
                    $message = $responseJson['message'] ?? ($response['message'] ?? null);
                    if ($code != 200 && intval($response['code']) != 0) {
                        if ($response['payment']['status'] == "INV") {
                            $message = "Transaction wasn't created successfully. Probably the merchant sent an incorrect parameter, so it wasn't possible to create the transaction correctly.";
                        } elseif ($response['payment']['status'] == "NEG") {
                            $message = "Transaction denied by the financial institution.";
                        }
                    }
                }
            } else { // sin respuesta
                $message = "error request";
            }
        } else {
            // solicitud no exitosa
        }

        return [
            'code' => $code,
            'error' => $error,
            'message' => $message,
            'confirmed' => $confirmed,
            // 'bodyTrans' => $bodyTrans,
            // 'response' => $response,
            // 'responseJson' => $responseJson
        ];
    }

    public function registrarTarjeta($datos)
    {
        if (is_null($datos) || !isset($datos['cliente']) || !isset($datos['numero']) || !isset($datos['mesanio']) || !isset($datos['cvv'])) {
            return [
                'code' => 500,
                'error' => true,
                'message' => "No se enviaron los parámetros para registrar la tarjeta de crédito."
            ];
        }

        $respLogin = $this->login();
        if ($respLogin['code'] == 200 && $respLogin['token']) {
            $headers = array(
                'Content-Type'  => 'application/json',
                'apikey'        => $this->apikey,
                'Authorization' => 'Bearer ' . $respLogin['token']
            );
            $bodyTarjeta = $this->bodyTarjeta;
            $bodyTarjeta['guid_user'] = $this->guidComercio;
            $bodyTarjeta['guid_cuenta'] = $this->guidCuenta;
            $bodyTarjeta['merchant_usn'] = $datos['cliente'];
            $bodyTarjeta['customer_id'] = $datos['cliente'];
            $bodyTarjeta['card_number'] = $datos['numero'];
            $bodyTarjeta['card_expiry_date'] = $datos['mesanio'];
            // Verificar Tipo de Tarjeta Visa o MasterCard
            // Visa: 1  MasterCard: 2
            $numTarjeta = str_split($datos['numero']);
            $bodyTarjeta['authorizer_id'] = ($numTarjeta[0] == 4) ? 1 : (($numTarjeta[0] == 5) ? 2 : null);
            if (is_null($bodyTarjeta['authorizer_id'])) {
                return [
                    'code' => 500,
                    'error' => true,
                    'message' => "Se requeire el tipo de Tarjeta (Visa o MasterCard)."
                ];
            }
            // return $bodyTarjeta;

            $code = null;
            $error = true;
            $token = null;
            $message = null;
            $responseJson = null;

            $jsonBodyTarjeta = $this->serializer->serialize($bodyTarjeta, "json");
            $urlCardStore = $this->urlApi . $this->endpointCardStore;
            $responseCardStore = UR\Request::post($urlCardStore, $headers, $jsonBodyTarjeta);

            // return $responseCardStore;
            if (isset($responseCardStore) && isset($responseCardStore->code)) {
                $code = $responseCardStore->code;
                if (isset($responseCardStore->raw_body)) {
                    $responseJson = json_decode($responseCardStore->raw_body, true);
                    $message = "OK. Transaction successful.";
                    if ($code == 200 && intval($responseJson['code']) == 0) {
                        $error = false;
                        $token = $responseJson['card']['token'];
                    } else { // error en solicitud
                        $message = $responseJson['message'] ?? ($response['message'] ?? null);
                    }
                }
            } else { // sin respuesta
                $message = "error request";
            }
        } else {
            // solicitud no exitosa
        }

        return [
            'code' => $code,
            'error' => $error,
            'token' => $token,
            'message' => $message,
            // 'bodyTarjeta' => $bodyTarjeta,
            // 'responseJson' => $responseJson
        ];
    }
}
