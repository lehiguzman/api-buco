<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Documento;
use App\Entity\Profesional;
use App\Entity\ProfesionalServicio;
use App\Entity\ProfesionalPreRegistro;
use App\Entity\ProfesionalMetodoPago;
use App\Entity\ProfesionalTarea;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ProfesionalPreRegistro Controller
 *
 * @Route("/api/v1/profesionales/preregistro")
 * @IsGranted("ROLE_USER")
 */
class ProfesionalPreRegistroController extends BaseAPIController
{
    /**
     * Lista los pre-registros completados del profesional.
     * @Rest\Get("/listado", name="profesional_preregistro_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista los pre-registros completados del profesional.",
     *     @Model(type=ProfesionalPreRegistro::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id, nombreCompleto, cedula, correo.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombreCompleto, cedula, correo.")
     * @SWG\Parameter(name="estado", in="path", type="string", description="Estado del Pre-Registro.")
     *
     * @SWG\Tag(name="Profesionales PreRegistro")
     * @IsGranted("ROLE_ADMIN")
     */
    public function getAllPreRegistroAction(Request $request)
    {
        try {
            $params = $request->query->all();
            $estado = isset($params['estado']) ? $params['estado'] : 2;
            // Campos con los que se podra ordenar
            $params['fields'] = ['id', 'nombreCompleto', 'cedula', 'correo'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->em->getRepository("App:ProfesionalPreRegistro")->findPreRegistros($estado, $params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Registra los datos de Pre-Registro del Profesional.
     * @Rest\Post("/actualizacion", name="profesional_preregistro_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Datos registrado satisfactoriamente.",
     *     @Model(type=ProfesionalPreRegistro::class)
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
     *     description="Campos del Pre-Registro del Profesional.",
     *     @SWG\Schema(
     *         @SWG\Property(property="servicioID", type="integer"),
     *         @SWG\Property(property="nombreCompleto", type="string"),
     *         @SWG\Property(property="correo", type="string"),
     *         @SWG\Property(property="cedula", type="string"),
     *         @SWG\Property(property="tlfCelular", type="string"),
     *         @SWG\Property(property="fechaNacimiento", type="string"),
     *         @SWG\Property(property="genero", type="integer"),
     *         @SWG\Property(property="nacionalidad", type="string"),
     *         @SWG\Property(property="areaCobertura", type="string"),
     *         @SWG\Property(property="aniosExperiencia", type="string"),
     *         @SWG\Property(property="vehiculo", type="boolean"),
     *         @SWG\Property(property="redesSociales", type="string"),
     *         @SWG\Property(property="especialidad", type="string"),
     *         @SWG\Property(property="camposEspecificos", type="string"),
     *         @SWG\Property(property="direccionID", type="integer"),
     *         @SWG\Property(property="tareasTarifas", type="string"),
     *         @SWG\Property(property="metodosPago", type="string"),
     *         @SWG\Property(property="documentos", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Profesionales PreRegistro")
     */
    public function addDatosPreRegistroAction(Request $request)
    {
        try {
            $servicioID = $request->request->get("servicioID", null);
            $nombreCompleto = $request->request->get("nombreCompleto", null);
            $correo = $request->request->get("correo", null);
            $cedula = $request->request->get("cedula", null);
            $tlfCelular = $request->request->get("tlfCelular", null);
            $fechaNacimiento = $request->request->get("fechaNacimiento", null);
            $genero = $request->request->get("genero", null);
            $nacionalidad = $request->request->get("nacionalidad", null);
            $areaCobertura = $request->request->get("areaCobertura", 0);
            $aniosExperiencia = $request->request->get("aniosExperiencia", 0);
            $vehiculo = $request->request->get("vehiculo", 'no');
            $redesSociales = $request->request->get("redesSociales", null);
            $especialidad = $request->request->get("especialidad", null);
            $camposEspecificos = $request->request->get("camposEspecificos", null);
            $direccionID = $request->request->get("direccionID", 0);
            $tareasTarifas = $request->request->get("tareasTarifas", null);
            $metodosPago = $request->request->get("metodosPago", null);
            $documentos = $request->request->get("documentos", null);

            if (trim($servicioID) == false) {
                $response['value'] = $servicioID;
                $response['info'] = "Por favor introduzca servicioID";

                return $this->JsonResponseBadRequest($response);
            }
            $Servicio = $this->em->getRepository("App:Servicio")->findOneBy([
                'id' => $servicioID,
                'estatus' => 1,
                'eliminado' => 0
            ]);
            if (is_null($Servicio)) {
                $response['value'] = $servicioID;
                $response['message'] = "Servicio no encontrado o no disponible";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($correo) == false) {
                $correo = $this->getUser()->getEmail();
            }

            if (trim($nombreCompleto) == false) {
                $nombreCompleto = $this->getUser()->getName();
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $PreRegistro = $this->em->getRepository("App:ProfesionalPreRegistro")->findOneBy([
                'user' => $this->getUser()->getId()
            ]);

            $code = 200;
            if (!$PreRegistro) {
                $code = 201;
                $PreRegistro = new ProfesionalPreRegistro();
            }

            if ($PreRegistro->getEstado() === 3) {
                $response['message'] = "Ya este Pre-Registro fue aprobado";
                return $this->JsonResponse($response);
            }

            if (trim($nombreCompleto)) {
                $PreRegistro->setNombreCompleto(trim($nombreCompleto));
            }

            if (trim($correo)) {
                $PreRegistro->setCorreo(trim($correo));
            }

            if (trim($cedula)) {
                $PreRegistro->setCedula(trim($cedula));
            }

            if (trim($tlfCelular)) {
                $PreRegistro->setTlfCelular(trim($tlfCelular));
            }

            if (trim($fechaNacimiento)) {
                $PreRegistro->setFechaNacimiento(trim($fechaNacimiento));
            }

            if (trim($genero)) {
                $genero = intval($genero) == 1 ? 'femenino' : 'masculino';
                $PreRegistro->setGenero(trim($genero));
            }

            if (trim($nacionalidad)) {
                $PreRegistro->setNacionalidad(trim($nacionalidad));
            }

            if (trim($areaCobertura)) {
                $PreRegistro->setAreaCobertura(trim($areaCobertura));
            }

            if (trim($aniosExperiencia)) {
                $PreRegistro->setAniosExperiencia(trim($aniosExperiencia));
            }

            if ($redesSociales) {
                $PreRegistro->setRedesSociales($redesSociales);
            }

            if (trim($especialidad)) {
                $PreRegistro->setEspecialidad(trim($especialidad));
            }

            if ($camposEspecificos) {
                $campES = [];
                foreach ($camposEspecificos as $key => $cE) {
                    $temp = [];
                    $clave = explode("__", array_keys($cE)[0])[0];
                    $temp[$clave] = [];
                    $temp[$clave]['nombre'] = $cE[$clave . "__nombre"];
                    $temp[$clave]['valor'] = $cE[$clave . "__valor"];
                    $temp[$clave]['formDinaId'] = $cE[$clave . "__formDinaId"];
                    $campES = array_merge($campES, $temp);
                }
                $PreRegistro->setCamposEspecificos($campES);
            }

            if ($direccionID) {
                $Direccion = $this->em->getRepository("App:Direccion")->findOneBy([
                    'id' => $direccionID,
                    'user' => $this->getUser()->getId(),
                    'eliminado' => 0
                ]);
                if (is_null($Direccion)) {
                    $response['value'] = $direccionID;
                    $response['message'] = "Dirección no encontrada o no disponible";
                    return $this->JsonResponseBadRequest($response);
                }                
                $PreRegistro->setDireccion($Direccion);

                //Actualiza el campo predeterminado en direccion
                $Direccion->setPredeterminada(1); 

                $this->em->persist($Direccion);
                $this->em->flush();
            }

            if ($tareasTarifas) {
                $PreRegistro->setTareasTarifas($tareasTarifas);
            }

            if ($metodosPago) {
                $PreRegistro->setMetodosPago($metodosPago);
            }

            if ($documentos) {
                $docsGuardados = $PreRegistro->getDocumentos();
                if (!$docsGuardados) {
                    $docsGuardados = [];
                }
                foreach ($documentos as $key => $doc) {
                    $resp = $this->guardarDocumento($doc);
                    if (is_array($resp) && isset($resp['response'])) {
                        if ($resp['response'] === "badrequest") {
                            return $this->JsonResponseBadRequest($resp);
                        } elseif ($resp['response'] === "exception") {
                            return $this->JsonResponseError($resp['exception'], 'exception');
                        } elseif ($resp['response'] === "validator") {
                            return $this->JsonResponseError($resp['validator'], 'validator');
                        }
                    } else {
                        $docsGuardados[] = $resp;
                    }
                }
                $PreRegistro->setDocumentos($docsGuardados);
            }

            $PreRegistro->setUser($this->getUser());
            $PreRegistro->setServicio($Servicio);
            $PreRegistro->setVehiculo($vehiculo);
            $PreRegistro->setFechaActualizado();

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($PreRegistro);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            // comprobar si los datos estan completos
            $PreRegistro = $this->formularioCompleto($PreRegistro);

            $this->em->persist($PreRegistro);
            $this->em->flush();

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($PreRegistro, $code);
    }

    /**
     * Obtiene los datos Pre-Registrados del Profesional.
     * @Rest\Get("/datos", name="profesional_preregistro_details")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene los datos Pre-Registrados del Profesional.",
     *     @Model(type=ProfesionalPreRegistro::class)
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
     *
     * @SWG\Tag(name="Profesionales PreRegistro")
     */
    public function getDatosPreRegistroAction()
    {
        try {
            $PreRegistro = $this->em->getRepository("App:ProfesionalPreRegistro")->findOneBy([
                'user' => $this->getUser()->getId()
            ]);
            if (!$PreRegistro || is_null($PreRegistro)) {
                $PreRegistro = null;
            } else {
                $PreRegistro = $this->formularioCompleto($PreRegistro);
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($PreRegistro);
    }

    /**
     * Establece el Pre-Registro como Aprobado o Rechazado.
     * @Rest\Post("", name="profesional_preregistro_aprobadorechazado")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Proceso Exitoso.",
     *     @Model(type=ProfesionalPreRegistro::class)
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
     *     description="Opciones: aprobado o rechazado.",
     *     @SWG\Schema(
     *         @SWG\Property(property="preregistroID", type="integer"),
     *         @SWG\Property(property="accion", type="string"),
     *         @SWG\Property(property="justificacion", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Profesionales PreRegistro")
     * @IsGranted("ROLE_ADMIN")
     */
    public function postAccionPreRegistroAction(Request $request)
    {
        try {
            $preregistroID = $request->request->get("preregistroID", null);
            $accion = $request->request->get("accion", null);
            $justificacion = $request->request->get("justificacion", null);

            if (trim($preregistroID) == false) {
                $response['value'] = $preregistroID;
                $response['info'] = "Por favor introduzca: preregistroID";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($accion) == false) {
                $response['value'] = $accion;
                $response['info'] = "Por favor introduzca un accion";
                return $this->JsonResponseBadRequest($response);
            }
            if (!in_array($accion, ['aprobado', 'rechazado'])) {
                $response['value'] = $accion;
                $response['info'] = "Las opciones son: aprobado o rechazado";
                return $this->JsonResponseBadRequest($response);
            }
            if (strcmp($accion, "rechazado") === 0) {
                if (trim($justificacion) == false) {
                    $response['value'] = $justificacion;
                    $response['info'] = "Por favor introduzca una justificacion";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $PreRegistro = $this->em->getRepository("App:ProfesionalPreRegistro")->find($preregistroID);
            if (is_null($PreRegistro)) {
                $response['value'] = $preregistroID;
                $response['message'] = "PreRegistro no encontrado";
                return $this->JsonResponseBadRequest($response);
            }
            if ($PreRegistro->getEstado() !== 2) {
                $response['message'] = "Ya este Pre-Registro fue aprobado o rechazado";
                return $this->JsonResponse($response);
            }

            if (strcmp($accion, "rechazado") === 0) {
                $PreRegistro->setEstado(0);
                $PreRegistro->setJustificacion($justificacion);
                $PreRegistro->setFechaRechazado();
            }

            $Profesional = null;
            if (strcmp($accion, "aprobado") === 0) {
                $PreRegistro->setEstado(3);
                $Profesional = $this->registrarProfesional($PreRegistro);
                $this->registrarProfesionalServicio($PreRegistro, $Profesional);
                $this->registrarMetodosPagoProfesional($PreRegistro, $Profesional);
                $this->registrarTareasProfesional($PreRegistro, $Profesional);
                $this->registrarDocumentosProfesional($PreRegistro, $Profesional);
                $PreRegistro->setFechaAprobado();
            }

            $this->em->persist($PreRegistro);
            $this->em->flush();

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        /** INICIO - ENVIO DE NOTIFICACIONES */
        try {
            $usuarioTokens = $this->gestionTokens->usuarioPushTokens($PreRegistro->getUser()->getId());

            if ($PreRegistro->getEstado() === 0) {
                $notificacion = ['¡Su Postulación como Profesional ha sido rechazada!', "Puede serguir en Buco como Cliente"];
            }

            if ($PreRegistro->getEstado() === 3) {
                $notificacion = ['¡Felicidades su registro como Profesional ha sido aprobado!', "Bienvenido a Buco"];
            }

            if (count($usuarioTokens) > 0) {
                $data = [];
                $data['nuevoprofesional'] = true;

                foreach ($usuarioTokens as $value) {
                    $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                }
            }
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE NOTIFICACIONES */

        /** INICIO - ENVIO DE CORREOS */
        try {
            $correos = [];
            if ($Profesional) $correos[$Profesional->getUser()->getEmail()] = $Profesional->getNombreCompleto();
            $correos[$PreRegistro->getCorreo()] = $PreRegistro->getNombreCompleto();
            $datos = [
                'profesional' => $Profesional,
                'preregistro' => $PreRegistro,
            ];
            $confCorreo = null;

            if ($PreRegistro->getEstado() === 0) {
                $confCorreo = [
                    'asunto' => "Pre-Registro del Profesional Rechazado",
                    'correos' => $correos,
                    'plantilla' => "email/profesional_rechazado.html.twig",
                    'datos' => $datos
                ];
            }

            if ($PreRegistro->getEstado() === 3) {
                $confCorreo = [
                    'asunto' => "Pre-Registro del Profesional Aprobado",
                    'correos' => $correos,
                    'plantilla' => "email/profesional_aprobado.html.twig",
                    'datos' => $datos
                ];
            }

            $this->enviarCorreo->enviar($confCorreo);
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE CORREOS */

        return $this->JsonResponseSuccess([
            'preregistro' => $PreRegistro,
            'profesional' => $Profesional
        ], 200);
    }

    // comprobar si los datos estan completos
    private function formularioCompleto($PreRegistro)
    {
        if (
            $PreRegistro->getUser() && $PreRegistro->getServicio() && $PreRegistro->getDireccion() &&
            $PreRegistro->getNombreCompleto() && $PreRegistro->getCedula() && $PreRegistro->getTlfCelular() &&
            $PreRegistro->getCorreo() && $PreRegistro->getGenero() &&
            $PreRegistro->getFechaNacimiento() && $PreRegistro->getNacionalidad() &&
            $PreRegistro->getAreaCobertura() && $PreRegistro->getAniosExperiencia() && $PreRegistro->getEspecialidad() &&
            $PreRegistro->getVehiculo() &&
            /*empty($PreRegistro->getCamposEspecificos()) === false && */empty($PreRegistro->getTareasTarifas()) === false && empty($PreRegistro->getDocumentos()) === false
        ) {
            $PreRegistro->setEstado(2); // formulario Completado
            $PreRegistro->setFechaCompletado();
        } else {
            $PreRegistro->setEstado(1); // formulario No Completo
        }
        return $PreRegistro;
    }

    // convertir el base64 a archivo y guardar
    private function guardarDocumento($documento)
    {
        $user = $this->getUser();
        $documento = (array) $documento;
        $doc_base64 = $documento['archivo'];
        $doc_nombre = $documento['nombre'];
        $doc_tipo = $documento['tipo_documento'];
        $dirAbsol = $this->getParameter('dir_uploads') . "documentos/preregistro/" . $user->getId() . "/";
        if (!file_exists($dirAbsol) && !is_dir($dirAbsol)) {
            mkdir($dirAbsol, 750, true);
        }

        $doc_base64 = explode(',', $doc_base64)[1];
        if (base64_decode($doc_base64, true) === false) {
            $response['info'] = "Error de formato base64";
            $response['response'] = "badrequest";
            return $response;
        }

        $decoded_file = base64_decode($doc_base64); // Decodifica el archivo
        $mime_type = finfo_buffer(finfo_open(), $decoded_file, FILEINFO_MIME_TYPE); // Extrae mime type
        $extension = $this->mime2ext($mime_type); // Extrae la extensión del archivo
        if (!in_array($extension, array('png', 'jpeg', 'pdf'))) {
            $response['value'] = $extension;
            $response['message'] = "La extensión del archivo debe ser png, jpeg ó pdf";
            $response['response'] = "badrequest";
            return $response;
        }

        $ruta = $dirAbsol . $this->generateRandomString() . '.' . $extension;
        try {
            $ifp = fopen($ruta, 'wb');
            fwrite($ifp, $decoded_file);
            fclose($ifp);
        } catch (Exception $ex) {
            $response['exception'] = $ex;
            $response['response'] = "exception";
            return $response;
        }

        $STD = $this->em->getRepository("App:ServicioTipoDocumento")->find($doc_tipo);

        $documento = new Documento();
        $documento->setProfesional(null);
        $documento->setNombre($doc_nombre);
        $documento->setRuta($ruta);
        $documento->setTipoDocumento($STD->getTipoDocumento());

        // Verificar datos de la Entidad
        $errors = $this->validator->validate($documento);
        if (count($errors) > 0) {
            $response['validator'] = $errors;
            $response['response'] = "validator";
            return $response;
        }

        $this->em->persist($documento);
        $this->em->flush();

        $documentoArray = [
            'id' => $documento->getId(),
            'ruta' => $documento->getRuta(),
            'nombre' => $documento->getNombre(),
            'tipo_documento' => $documento->getTipoDocumento()->getId(),
            'tipo_documento_nombre' => $documento->getTipoDocumento()->getNombre(),
        ];

        return $documentoArray;
    }

    private function registrarProfesional($PreRegistro)
    {
        $Profesional = new Profesional();
        $nombreCompleto = $PreRegistro->getNombreCompleto();
        $expNombre = explode(" ", $nombreCompleto);
        $nombre = $nombreCompleto;
        $apellido = $nombreCompleto;
        if (count($expNombre) === 4) {
            $nombre = $expNombre[0] . " " . $expNombre[1];
            $apellido = $expNombre[2] . " " . $expNombre[3];
        }
        if (count($expNombre) === 3) {
            $nombre = $expNombre[0] . " " . $expNombre[1];
            $apellido = $expNombre[2];
        }
        if (count($expNombre) === 2) {
            $nombre = $expNombre[0];
            $apellido = $expNombre[1];
        }

        $PreRegistro->getUser()->setRoles("ROLE_PROFESIONAL");
        $Profesional->setUser($PreRegistro->getUser());
        $Profesional->setServicio($PreRegistro->getServicio());
        $Profesional->setNombre($nombre);
        $Profesional->setApellido($apellido);
        $Profesional->setIdentificacion($PreRegistro->getCedula());
        $Profesional->setNacionalidad($PreRegistro->getNacionalidad());
        $Profesional->setTelefono($PreRegistro->getTlfCelular());
        $Profesional->setDireccion($PreRegistro->getDireccion()->getDireccion());
        $Profesional->setLatitud($PreRegistro->getDireccion()->getLatitud());
        $Profesional->setLongitud($PreRegistro->getDireccion()->getLongitud());
        $Profesional->setAniosExperiencia($PreRegistro->getAniosExperiencia());
        $Profesional->setDestrezaDetalle($PreRegistro->getEspecialidad());
        $Profesional->setRadioCobertura(floatval($PreRegistro->getAreaCobertura()));
        $Profesional->setRedesSociales($PreRegistro->getRedesSociales());
        $Profesional->setVehiculo($PreRegistro->getVehiculo());

        $datosEspecailes = [];
        foreach ($PreRegistro->getCamposEspecificos() as $key => $campo) {
            $formDina = $this->em->getRepository("App:FormularioDinamico")->find($campo['formDinaId']);
            $datosEspecailes[] = [
                'formdina_id' => $formDina->getId(),
                'formdina_tipo' => $formDina->getTipo(),
                'nombre' => $campo['nombre'],
                'valor' => $this->formatoDatoFechaHora($campo['valor'], $formDina->getTipo()),
            ];
        }
        $Profesional->setDatosEspecificos($datosEspecailes);        

        $this->em->persist($PreRegistro->getUser());
        $this->em->persist($Profesional);

        return $Profesional;
    }

    private function formatoDatoFechaHora($fechahora, $tipocampo)
    {
        switch ($tipocampo) {
            case 'datetime':
                $fechahora = new \Datetime($fechahora);
                $fechahora = $fechahora->format('d/m/Y h:i A');
                break;
            case 'date':
                $fechahora = new \Datetime($fechahora);
                $fechahora = $fechahora->format('d/m/Y');
                break;
            case 'time':
                $fechahora = new \Datetime($fechahora);
                $fechahora = $fechahora->format('h:i A');
                break;
        }
        return $fechahora;
    }

    private function registrarProfesionalServicio($PreRegistro, $Profesional) {               

        $ProfesionalServicio = new ProfesionalServicio();
        $ProfesionalServicio->setProfesional($Profesional);
        $ProfesionalServicio->setServicio($PreRegistro->getServicio());
        $ProfesionalServicio->setEstado(1);

        $this->em->persist($ProfesionalServicio);

        return true;
    }

    private function registrarMetodosPagoProfesional($PreRegistro, $Profesional) {
        foreach ($PreRegistro->getMetodosPago() as $key => $metodoPago) {
            $MetodoPago = $this->em->getRepository("App:MetodoPago")->find($metodoPago['id']);

            $ProfesionalMetodoPago = new ProfesionalMetodoPago();            
            $ProfesionalMetodoPago->setProfesional($Profesional);
            $ProfesionalMetodoPago->setMetodoPago($MetodoPago);

            $this->em->persist($ProfesionalMetodoPago);
        }

        return true;
    }

    private function registrarTareasProfesional($PreRegistro, $Profesional)
    {
        foreach ($PreRegistro->getTareasTarifas() as $key => $tarea) {
            $Tarea = $this->em->getRepository("App:Tarea")->find($tarea['tareaID']);

            $ProfesionalTarea = new ProfesionalTarea();
            $ProfesionalTarea->setTarea($Tarea);
            $ProfesionalTarea->setProfesional($Profesional);
            $ProfesionalTarea->setPrecio(floatval($tarea['valor']));

            $this->em->persist($ProfesionalTarea);
        }

        return true;
    }

    private function registrarDocumentosProfesional($PreRegistro, $Profesional)
    {
        foreach ($PreRegistro->getDocumentos() as $key => $doc) {
            $Documento = $this->em->getRepository("App:Documento")->find($doc['id']);
            $Documento->setProfesional($Profesional);
            $this->em->persist($Documento);
        }

        return true;
    }
}
