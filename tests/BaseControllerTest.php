<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Módulos Activos para pruebas con valor TRUE
define("MODULO_AUTH", TRUE);
define("MODULO_EJEMPLO", TRUE);
define("PRUEBA_AMBIENTES", TRUE);
define("MODULO_CLIENTE_TARJETAS", TRUE);
define("MODULO_PROFESIONAL_METODOPAGO", FALSE);

# https://symfony.com/doc/current/testing.html
# https://phpunit.readthedocs.io/en/8.5/assertions.html
class BaseControllerTest extends WebTestCase
{
    protected $ENDPOINT;
    protected $RESPONSE_API_CODE = [200, 201, 204, 400, 401, 403, 404, 500];
    protected $RESPONSE_API_FORBIDDEN = FALSE;
    protected $RESPONSE_API_MESSAGE;

    protected $beProhibited = FALSE;
    protected $client;
    protected $invalid = FALSE;
    protected $token = "token null";
    protected $urlAPI;

    // Errores
    private $errorArray = ["El Formato de respuesta no es válido | "];

    /**
     * Método simple y genérico
     */
    public function testIndex()
    {
        $this->assertTrue(true);
    }

    /**
     * Método para setear parámetros generales
     */
    public function prepare()
    {
        # https://symfony.com/doc/4.4/testing.html#working-with-the-test-client
        $this->client = static::createClient();
        $container = self::$container;
        $this->urlAPI = $container->getParameter('api.url');
        $this->invalid = false;
    }

    /**
     * Método para config los Headers
     */
    public function getHeaders()
    {
        if (!$this->invalid) $this->assertNotEquals($this->token, "token null", "\t el token no es válido");
        if (strcmp("token null", $this->token) === 0) return null;

        $header = [];
        $header["HTTP_CONNECTION"] = "keep-alive";
        $header["HTTP_CONTENT_TYPE"] = "application/json";
        $header["HTTP_AUTHORIZATION"] = "Bearer {$this->token}";

        return $header;
    }

    /**
     * Método para loguearse (otener token)
     *
     * @param  array $credenciales
     * @return object
     */
    public function loginApi($credenciales)
    {
        $urlCompleta = $this->urlAPI . '/api/login_check';
        // printf("\n -=> POST login url \n" . $urlCompleta . "\n");
        $username = $credenciales['_username'];
        $this->client->request('POST', $urlCompleta, $credenciales, [], []);
        $this->beProhibited = (strcmp('forbidden', $credenciales['permissions']) === 0);
        $jsonResponse = null;
        $responseContent = $this->client->getResponse()->getContent();
        $responseStatusCode = $this->client->getResponse()->getStatusCode();
        if (isset($responseContent)) {
            $jsonResponse = json_decode($responseContent);
        }

        // printf("\n -=> POST login \n" . $responseContent . "\n");
        // printf("\n -=> POST login \n" . $responseStatusCode . "\n");

        if ($this->invalid && (strpos($responseContent, '401') !== false || strpos($responseContent, 'Invalid credentials') !== false)) {
            $this->assertEquals(401, $jsonResponse->code);
            $this->assertEquals(401, $responseStatusCode, $username . "\tCredenciales inválidas");
            $this->assertIsObject($jsonResponse);
            $this->assertIsString($jsonResponse->message);
            $this->assertNotNull($jsonResponse->message);
            $this->assertStringContainsStringIgnoringCase("Invalid credentials", $responseContent);

            $this->token = "token null";
        } else {
            $this->assertEquals(200, $responseStatusCode, $username . "\tLogin no exitoso -- $urlCompleta");
            $this->assertIsObject($jsonResponse, $this->errorArray[0] . " -- $urlCompleta");
            $this->assertIsString($jsonResponse->token, "Token no es string -- $urlCompleta");
            $this->assertStringContainsStringIgnoringCase("token", $responseContent, "Propiedad token no encontrada -- $urlCompleta");

            $this->token = $jsonResponse->token;
        }
    }

    /**
     * Método de config de pre solicitud
     */
    private function preRequest($options)
    {
        $HEADERS = $this->getHeaders();
        $this->RESPONSE_API_FORBIDDEN = false;
        $this->ENDPOINT = $this->urlAPI . $options['pathModule'] . $options['endpoint'];
        $BODY = key_exists('body', $options) ? $options['body'] : [];

        $METHOD = 'GET';
        switch ($options['method']) {
            case 'get':
                $METHOD = 'GET';
                break;
            case 'post':
                $METHOD = 'POST';
                break;
            case 'put':
                $METHOD = 'PUT';
                break;
            case 'delete':
                $METHOD = 'DELETE';
                break;
        }

        $client = static::createClient();
        $client->request($METHOD, $this->ENDPOINT, $BODY, [], $HEADERS);
        $responseContent = $client->getResponse()->getContent();
        $responseStatusCode = $client->getResponse()->getStatusCode();
        $response = json_decode($responseContent);
        // var_dump($responseContent);

        $METHOD_ENDPOINT = "($METHOD) $this->ENDPOINT";

        if (strpos($responseContent, '403 Forbidden') !== false || strpos($responseContent, 'Access Denied') !== false) {
            $this->assertEquals($responseStatusCode, 403);
            $this->RESPONSE_API_FORBIDDEN = true;
            $this->assertEquals($this->beProhibited, $this->RESPONSE_API_FORBIDDEN);

            return [
                'forbidden' => true,
            ];
        } else {
            $this->assertIsObject($response, "response not is object | $METHOD_ENDPOINT");
            $this->assertIsString($response->message, "message not is string | $METHOD_ENDPOINT");
            $this->assertNotNull($response->message, "message is null | $METHOD_ENDPOINT");
            $this->assertIsBool($response->error, "error not is boolean | $METHOD_ENDPOINT");
            $this->assertEquals($responseStatusCode, $response->code, "status code invalid | $METHOD_ENDPOINT");
            $this->assertEquals($responseStatusCode, $response->status, "status code invalid | $METHOD_ENDPOINT");
            $this->assertContains($responseStatusCode, $this->RESPONSE_API_CODE, "\tstatus code invalid | $METHOD_ENDPOINT");
        }

        $this->RESPONSE_API_MESSAGE = "\nMessage: ";
        if (is_array($response->message)) {
            foreach ($response->message as $msg) {
                if (isset($msg->value) && isset($msg->info)) {
                    $this->RESPONSE_API_MESSAGE .= "\n\t Valor $msg->value:$msg->info";
                }
            }
        } else {
            $this->RESPONSE_API_MESSAGE = $response->message ?? "";
        }
        // errors validator
        if ($response->code === 400) {
            if (isset($response->description) && is_array($response->description)) {
                foreach ($response->description as $dscpt) {
                    if (isset($dscpt->property) && isset($dscpt->message)) {
                        $this->RESPONSE_API_MESSAGE .= "\n\t Campo $dscpt->property:$dscpt->message";
                    }
                }
            }
        }
        $this->RESPONSE_API_MESSAGE .= "\n";

        return [
            'client' => $client,
            'content' => $responseContent,
            'statusCode' => $responseStatusCode,
            'response' => $response,
            'methodendpoint' => $METHOD_ENDPOINT,
        ];
    }

    /**
     * Método Genérico para consultar - listado (GET)
     */
    public function getListGenerico($options, $type)
    {
        $options['method'] = 'get';
        $clientResp = $this->preRequest($options);
        if (isset($clientResp['forbidden'])) return $clientResp;
        $statusCode = $clientResp['statusCode'];
        $response = $clientResp['response'];
        $MSG = $clientResp['methodendpoint'] . "\n" . $this->RESPONSE_API_MESSAGE .  "\n";

        if (!$this->notFound404($response, $options)) {
            $this->assertEquals(200, $statusCode, "ERROR CONSULTA | $MSG");
            if (strcmp($type, "list") === 0) {
                $this->assertIsIterable($response->data, "No es un tipo de dato iterable | " . $MSG);
                $respMsg = count($response->data) > 0 ? "¡Registro(s) encontrado(s)!" : "¡Ningún registro encontrado!";
                $this->assertStringContainsString($respMsg, $response->message, $MSG);
            } elseif (strcmp($type, "details") === 0) {
                $this->assertIsObject($response->data, "No es objeto | " . $MSG);
                $this->comprobacionesGenericas($response->data);
                $this->assertStringContainsString("¡Registro encontrado!", $response->message, $MSG);
            }
        }

        return [
            'statusCode' => $statusCode,
            'response' => $response,
        ];
    }

    /**
     * Método Genérico para registrar - registro (POST)
     */
    public function postCreateGenerico($options)
    {
        $options['method'] = 'post';
        $clientResp = $this->preRequest($options);
        if (isset($clientResp['forbidden'])) return $clientResp;
        $content = $clientResp['content'];
        $statusCode = $clientResp['statusCode'];
        $response = $clientResp['response'];
        $MSG = $clientResp['methodendpoint'] . "\n" . $this->RESPONSE_API_MESSAGE .  "\n";

        if ($response->code === 400 && strpos($response->message, "SiTef offline") !==  false && $response->error) {
            $this->assertEquals(400, $response->code, "Error al registrar | " . $options['endpoint']);
            $this->assertIsString($response->message);
            $this->assertIsBool($response->error);
            return null;
        }

        $this->assertEquals(201, $statusCode, "ERROR REGISTRO | $MSG");
        $this->assertIsObject($response->data, "No es objeto | " . $MSG);
        foreach ($options['body'] as $value) {
            $this->comprobacionesGenericas($value);
            // validaciones personalizadas por módulo
            $modulo = strpos($options['pathModule'], "clientes/tarjetas") ?? null;
            if (is_null($modulo)) {
                $this->assertStringContainsStringIgnoringCase($value, $content);
            }
        }
        $this->assertStringContainsString("¡Registro exitoso!", $response->message, $MSG);

        return [
            'statusCode' => $statusCode,
            'response' => $response->data,
        ];
    }

    /**
     * Método Genérico para editar - edición (PUT)
     */
    public function putEditGenerico($options)
    {
        $options['method'] = 'put';
        $clientResp = $this->preRequest($options);
        if (isset($clientResp['forbidden'])) return $clientResp;
        $content = $clientResp['content'];
        $statusCode = $clientResp['statusCode'];
        $response = $clientResp['response'];
        $MSG = $clientResp['methodendpoint'] . "\n" . $this->RESPONSE_API_MESSAGE .  "\n";

        $this->assertEquals(200, $statusCode, "ERROR EDICIÓN | $MSG");
        $this->assertIsObject($response->data, "No es objeto | " . $MSG);
        $this->comprobacionesGenericas($response->data);
        foreach ($options['body'] as $key => $value) {
            // validaciones campos de la entidad
            $modulo = strpos($options['body'][$key], "_password") ?? null;
            if (is_null($modulo)) {
                $this->assertStringContainsStringIgnoringCase($value, $content);
            }
        }
        $this->assertStringContainsString("¡Registro modificado con éxito!", $response->message, $MSG);

        return [
            'statusCode' => $statusCode,
            'response' => $response->data,
        ];
    }

    /**
     * Método Genérico para eliminar - borrar (DELETE)
     */
    public function deleteDataGenerico($options)
    {
        $options['method'] = 'delete';
        $clientResp = $this->preRequest($options);
        if (isset($clientResp['forbidden'])) return $clientResp;
        $statusCode = $clientResp['statusCode'];
        $response = $clientResp['response'];
        $MSG = $clientResp['methodendpoint'] . "\n" . $this->RESPONSE_API_MESSAGE .  "\n";

        $this->assertContains($statusCode, [200, 204], "ERROR EDICIÓN | $MSG");
        $this->assertIsObject($response->data, "No es objeto | " . $MSG);
        $this->assertStringContainsString("¡Registro eliminado con éxito!", $response->message, $MSG);

        return [
            'statusCode' => $statusCode,
            'response' => $response->data,
        ];
    }

    /**
     * Método Genérico para retornar 404
     */
    private function notFound404($response, $options)
    {
        if (isset($response->code) && $response->code === 404) {
            $this->assertEquals(404, $response->code);
            $this->assertEquals(404, $response->status);
            $this->assertStringContainsString("¡Registro no encontrado!", $response->message, $options['methodendpoint']);
            return true;
        } else {
            return false;
        }
    }

    public function cURL($method, $url, $body, $headers)
    {
        $curl = curl_init();
        $arr_curl_opt = array();

        if (strcmp('POST', $method) == 0) {
            $arr_curl_opt = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => $headers,
                // CURLOPT_HTTPHEADER => array(
                //     "Content-Type: application/json",
                //     "merchant_id: soluciones",
                //     "merchant_key: BB5047AB57F593762A7816A12DDE1DE8B2AE08FB226B4E0B12645E2965B4E3BE"
                // ),
            );
        } else {
            $arr_curl_opt = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => $headers,
                // CURLOPT_HTTPHEADER => array(
                //     "Content-Type: application/json",
                //     "merchant_id: soluciones",
                //     "merchant_key: BB5047AB57F593762A7816A12DDE1DE8B2AE08FB226B4E0B12645E2965B4E3BE"
                // ),
            );
        }

        curl_setopt_array($curl, $arr_curl_opt);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Método Genérico para comprobar campos compartidos
     */
    function comprobacionesGenericas($registro)
    {
        if (isset($registro->id)) {
            $this->assertIsInt($registro->id);
        }
        if (isset($registro->estado)) {
            $this->assertNotNull($registro->estado);
            $this->assertIsInt($registro->estado);
        }
        if (isset($registro->fechaRegistro)) {
            $this->assertNotNull($registro->fechaRegistro);
        }
        if (isset($registro->fechaEliminado)) {
            $this->assertNull($registro->fechaEliminado);
        }
        if (isset($registro->eliminado)) {
            $this->assertIsBool($registro->eliminado);
        }
    }
}
