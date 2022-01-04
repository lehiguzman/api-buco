<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// Service de Envio de Correos
use App\Service\SendEmails;
use App\Service\EnvioCorreoServicio;
// Service de Gestion de Afiliados
use App\Service\GestionPushTokens;
// Service de Firebase
use App\Service\FirebaseServicio;
// Service de Calculo Comisión Buconexión
use App\Service\CalcularComisionBuconexion;
// Service de Verificación de disponibilidad de Profesional
use App\Service\VerificarDisponibilidad;

// App Insights
use AppInsightsPHP\Client\Client;
use AppInsightsPHP\Client\ClientFactory;
use AppInsightsPHP\Client\Configuration;
use AppInsightsPHP\Symfony\AppInsightsPHPBundle\Cache\NullCache;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

/**
 * Esta clase funciona como la Base de todos los Controllers del API
 * 
 * BaseController sirve para controllar las respuesta de los endpoints
 * Response con code: 200, 201, 204, 400, 401, 403, 404, 500
 * 
 * Métodos para pre-formatear las consultas a las base de datos
 * 
 * Referencias:
 * https://blog.restcase.com/5-basic-rest-api-design-guidelines/
 * 
 * @author Strapp International Inc.
 */
class BaseController extends AbstractFOSRestController
{
    protected $em;
    protected $encoder;
    protected $validator;
    protected $mailer;
    protected $enviarCorreo;
    protected $gestionTokens;
    protected $firebase;
    protected $calcularBuconexion;
    protected $verificarDisponibilidad;
    protected $serializer;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        SendEmails $sendEmails,
        EnvioCorreoServicio $enviarCorreo,
        GestionPushTokens $gestionTokens,
        FirebaseServicio $firebase,
        CalcularComisionBuconexion $calcularBuconexion,
        VerificarDisponibilidad $verificarDisponibilidad,
        SerializerInterface $serializer
    ) {
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->em = $entityManager;
        $this->mailer = $sendEmails;
        $this->enviarCorreo = $enviarCorreo;
        $this->gestionTokens = $gestionTokens;
        $this->firebase = $firebase;
        $this->calcularBuconexion = $calcularBuconexion;
        $this->verificarDisponibilidad = $verificarDisponibilidad;
        $this->serializer = $serializer;
    }

    /**
     * Método genérico de response (SOLO PARA PRUEBAS)
     */
    public function JsonResponse($data)
    {
        $response = [];
        $response['data'] = $data;
        $response['error'] = false;

        return new JsonResponse($this->serializer->serialize($response, 'json'), 200, [], true);
    }

    /**
     * Método genérico que controla y retornar peticiones exitosas del API
     */
    public function JsonResponseSuccess($data, $code = 200, $msg = NULL)
    {
        $response = [];
        $response['code'] = $code;
        $response['status'] = $code;
        $response['data'] = $data;
        $response['message'] = $msg ?? "¡Consulta Exitosa!";
        $response['error'] = false;

        if ($code == 200 && gettype($data) === 'array') {
            $response['message'] = count($data) > 0 ? "¡Registro(s) encontrado(s)!" : "¡Ningún registro encontrado!";
            $response['totalRecords'] = count($data);
        } elseif ($code == 200 && (is_null($data) || empty($data))) {
            $response['message'] = $msg ?? "¡Sin contenido!";
        } elseif ($code == 200 && is_null($msg) && gettype($data) === 'object') {
            $response['message'] = $msg ?? "¡Registro encontrado!";
        }

        if ($code == 201) {
            $response['message'] = "¡Registro exitoso!";
        }

        return new JsonResponse($this->serializer->serialize($response, 'json'), $code, [], true);
    }

    /**
     * Método genérico que controla y retornar los erroes de Peticiones Malas
     */
    public function JsonResponseBadRequest($data)
    {
        $response = [];
        $response['code'] = 400;
        $response['status'] = 400;
        $response['message'] = "¡Error, por favor ingrese parámetro(s) faltante(s)!";
        $response['error'] = true;

        if (key_exists('info', $data)) {
            $response['info'] = $data['info'];
        }

        if (key_exists('value', $data)) {
            $response['value'] = $data['value'];
        }

        if (key_exists('message', $data)) {
            $response['message'] = $data['message'];
        } elseif (key_exists('info', $data)) {
            $response['message'] = $data['info'];
        }

        return new JsonResponse($this->serializer->serialize($response, 'json'), 400, [], true);
    }

    /**
     * Método genérico que controla y retorna los errores de Acceso Denegado
     */
    public function JsonResponseAccessDenied($message = null)
    {
        $response = [];
        $response['code'] = 403;
        $response['status'] = 403;
        $response['message'] = $message ?? "¡Lo siento; usted no tiene permiso para acceder a este recurso!";
        $response['error'] = true;

        return new JsonResponse($this->serializer->serialize($response, 'json'), 403, [], true);
    }

    /**
     * Método genérico para registro no encontrado del API
     */
    public function JsonResponseNotFound($msg = NULL)
    {
        $response = [];
        $response['code'] = 404;
        $response['status'] = 404;
        $response['message'] = $msg ?? "¡Registro no encontrado!";
        $response['error'] = false;

        return new JsonResponse($this->serializer->serialize($response, 'json'), 404, [], true);
    }

    /**
     * Método genérico que controla y retorna los diferentes errores del API
     */
    public function JsonResponseError($data, $option, $appInsightsOptions = array())
    {
        $response = [];
        $response['code'] = 500;
        $response['status'] = 500;
        $response['message'] = $data["message"] ?? "¡Error en Servidor!";
        $response['error'] = true;

        $instrumentation_key = $this->getParameter('instrumentation_key');
        $appInsights = (new \AppInsightsPHP\Client\ClientFactory(
            $instrumentation_key,
            \AppInsightsPHP\Client\Configuration::createDefault(),
            new NullCache(),
            new NullLogger()
        ))->create();

        if ($option == 'exception') {
            $response['message'] = "Ocurrió un error en el servidor";
            $response['exception'] = $data->getMessage();
            $response['type'] = 'exception';
            if (count($appInsightsOptions)) {
                $appInsights->trackEvent(
                    $appInsightsOptions["name"],
                    [
                        'EndPointName' => $appInsightsOptions["EndPointName"],
                        'EndPointURL'  => $appInsightsOptions["EndPointURL"],
                        'error' => $data->getMessage()
                    ]
                );
                $appInsights->flush();
                $response['appInsights'] = $appInsights;
            }
        }

        if ($option == 'validator') {
            $response['message'] = "Error en uno de los campos del registro";
            $response['description'] = $this->formatValidationErrors($data);
            $response['code'] = 400;
            $response['status'] = 400;
            $response['type'] = 'validator';
        }

        if ($option == 'check_parameters') {
            $response['value'] = $data['value'];
            $response['message'] = $data['message'];
            $response['type'] = 'check_parameters';
            $response['code'] = 400;
            $response['status'] = 400;
        }

        if ($option == 'file') {
            $response['message'] = "Ocurrió un error en el Servidor";
            $response['exception'] = $data['message'];
            $response['type'] = 'exception';
        }

        return new JsonResponse($this->serializer->serialize($response, 'json'), $response['code'], [], true);
    }

    /**
     * Formatea el objeto proporcionado por el validator
     *
     * @param  object $data
     * @return array $errors
     */
    private function formatValidationErrors($data)
    {
        $errors = [];

        foreach ($data as $value) {
            $errors[] = [
                "property" => $value->getPropertyPath(),
                "message" => $value->getMessage(),
            ];
        }

        return $errors;
    }

    /**
     * Query Processor
     * 
     * Keys:
     *    "field":    campos a ordenar
     *    "sort":     orden de la consulta
     *    "search":   dato a buscar
     *    "page":     numero de pagina a consultar
     *    "per_page": numero de registro por consulta
     *    "filtro:    filtros flat, range y list
     * 
     * params    arreglo de claves
     */
    public function QueryProcessor(array $params)
    {
        $_params = [];

        if (key_exists('sort', $params)) {
            $sort = preg_replace('/\s+/', '', strtolower($params['sort']));
            if ($sort === 'asc' or $sort === 'desc') {
                $_params['sort'] = strtoupper($sort);
            }
        }

        if (key_exists('field', $params)) {
            $field = preg_replace('/\s+/', '', $params['field']);
            $fields = [];
            if (key_exists('fields', $params)) {
                $fields = $params['fields'];
            }
            if (in_array($field, $fields)) {
                $_params['field'] = $field;
            }
        }

        if (key_exists('limit', $params)) {
            $limit = preg_replace('/\s+/', '', $params['limit']);
            if (intval($limit)) {
                $_params['limit'] = intval($limit);
            }
        }

        if (key_exists('search', $params)) {
            $_params['search'] = preg_replace('/\s+/', '', $params['search']);
        }

        if (key_exists('searchProfesional', $params)) {
            $_params['searchProfesional'] = preg_replace('/\s+/', '', $params['searchProfesional']);
        }

        if (key_exists('findByEstatus', $params)) {
            $estatus = preg_replace('/\s+/', '', $params['findByEstatus']);
            if (intval($estatus)) {
                $_params['findByEstatus'] = intval($estatus);
            }
        }

        if (key_exists('findByServicio', $params)) {
            $servicio_id = preg_replace('/\s+/', '', $params['findByServicio']);
            if (intval($servicio_id)) {
                $_params['findByServicio'] = intval($servicio_id);
            }
        }

        if (key_exists('findByFechas', $params)) {
            $findByFechas = preg_replace('/\s+/', '', $params['findByFechas']);
            if (intval($findByFechas)) {
                if (key_exists('desde', $params) && key_exists('hasta', $params)) {
                    $_params['findByFechas'] = intval($findByFechas);
                    $_params['desde'] = preg_replace('/\s+/', '', $params['desde']);
                    $_params['hasta'] = preg_replace('/\s+/', '', $params['hasta']);
                }
            }
        }

        return $_params;
    }

    /**
     * Método para generar una cadena aleatoria
     */
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Validar contraseñas
     */
    public function validPassword($pswd)
    {
        $response = [];
        $response['value'] = $pswd;

        if (strlen(trim($pswd)) < 8) {
            $response['message'] = "La clave no puede ser menor a 8 caracteres";
        } elseif (strlen(trim($pswd)) > 15) {
            $response['message'] = "La clave no puede ser mayor a 15 caracteres";
        } elseif (!preg_match('/([A-Z])/', trim($pswd))) {
            $response['message'] = "La clave debe tener al menos una mayúscula";
        } elseif (!preg_match('/([a-z])/', trim($pswd))) {
            $response['message'] = "La clave debe tener al menos una minúscula";
        } elseif (!preg_match('/([0-9])/', trim($pswd))) {
            $response['message'] = "La clave debe tener al menos un número";
        } elseif (!preg_match('/([!,%,&,@,#,$,^,*,?,_,~])/', trim($pswd))) {
            $response['message'] = "La clave debe tener al menos un caracter especial";
        }

        return $response;
    }

    /*
     * Retorna la extensión a partir del mime type
    */
    public function mime2ext($mime)
    {
        $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp",
        "image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp",
        "image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp",
        "application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg",
        "image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],
        "wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],
        "ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg",
        "video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],
        "kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],
        "rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application",
        "application\/x-jar"],"zip":["application\/x-zip","application\/zip",
        "application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],
        "7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],
        "svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],
        "mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],
        "webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],
        "pdf":["application\/pdf","application\/octet-stream"],
        "pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],
        "ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office",
        "application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],
        "xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],
        "xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel",
        "application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],
        "xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo",
        "video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],
        "log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],
        "wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],
        "tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop",
        "image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],
        "mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar",
        "application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40",
        "application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],
        "cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary",
        "application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],
        "ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],
        "wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],
        "dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php",
        "application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],
        "swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],
        "mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],
        "rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],
        "jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],
        "eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],
        "p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],
        "p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';

        $all_mimes = json_decode($all_mimes, true);

        foreach ($all_mimes as $key => $value) {
            if (array_search($mime, $value) !== false) return $key;
        }

        return false;
    }
}
