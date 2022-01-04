<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Documento;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Documento Controller
 *
 * @Route("/api/v1/documentos")
 * @IsGranted("ROLE_PROFESIONAL")
 */
class DocumentoController extends BaseAPIController
{
    /**
     * Lista los documentos
     * @Rest\Get("", name="documento_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los documentos",
     *     @Model(type=Documento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los documentos"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Documentos")
     */
    public function getAllDocumentoAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->em->getRepository("App:Documento")->findDocumentos($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * obtener la información de un documento dado el ID
     * @Rest\Get("/{id}", name="documento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el documento basado en parámetro ID.",
     *     @Model(type=Documento::class)
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
     *     description="El documento ID"
     * )
     *
     *
     * @SWG\Tag(name="Documentos")
     */
    public function getDocumentoAction(Request $request, $id)
    {
        try {
            $documento = $this->em->getRepository("App:Documento")->findDocumentoActivo($id);
            if (!$documento || is_null($documento)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        /*
        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $documento->getRuta();
        $dir_uploads = $this->getParameter('dir_uploads') . $documento->getRuta();
        if ($documento->getRuta() && file_exists($dir_uploads)) {
            $documento->setRuta($uploads);
        } else {
            $documento->setRuta($dir_uploads);
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $documento->getCopia();
        $dir_uploads = $this->getParameter('dir_uploads') . $documento->getCopia();
        if ($documento->getCopia() && file_exists($dir_uploads)) {
            $documento->setCopia($uploads);
        } else {
            $documento->setCopia('');
        }
        */

        return $this->JsonResponseSuccess($documento);
    }

    /**
     * agregar un documento
     * @Rest\Post("", name="documento_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Documento agregado exitosamente",
     *     @Model(type=Documento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrio un error agregando un nuevo documento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del documento",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="Ruta del documento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipo_documento",
     *     in="body",
     *     type="integer",
     *     description="Tipo de documento",
     *     schema={},
     *     required=true
     * )     
     * 
     * @SWG\Parameter(
     *     name="profesional",
     *     in="body",
     *     type="integer",
     *     description="Id de profesional",
     *     schema={},
     *     required=true
     * )     
     *
     * @SWG\Parameter(
     *     name="copia",
     *     in="body",
     *     type="string",
     *     description="Copia del documento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaVencimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de vencimiento del documento en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="archivo",
     *     in="body",
     *     type="string",
     *     description="documento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Documentos")
     */
    public function addDocumentoAction(Request $request)
    {
        $em = $this->em;
        try {
            $nombre = $request->request->get('nombre', null);
            $ruta = $request->request->get('ruta', null);
            $tipo_documento_id = $request->request->get('tipo_documento', null);
            $profesional_id = $request->request->get('profesional', null);
            $copia = $request->request->get('copia', null);
            $fechaVencimiento = $request->request->get("fechaVencimiento", null);
            $archivo = $request->request->get("archivo", null);

            if (trim($ruta) == false) {
                $response['value'] = $ruta;
                $response['message'] = "Por favor introduzca la ruta del documento";
                return $this->JsonResponseBadRequest($response);
            }

            if (is_null($tipo_documento_id)) {
                $response['value'] = $tipo_documento_id;
                $response['message'] = "Por favor introduzca el tipo documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $tipo_documento = $em->getRepository("App:TipoDocumento")->findTipoDocumentoActivo($tipo_documento_id);
                if (!$tipo_documento) {
                    $response['value'] = $tipo_documento_id;
                    $response['message'] = "Tipo de documento no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (is_null($profesional_id)) {
                $response['value'] = $profesional_id;
                $response['message'] = "Por favor introduzca el profesional";
                return $this->JsonResponseBadRequest($response);
            } else {
                $profesional = $em->getRepository("App:Profesional")->findProfesionalActivo($profesional_id);
                if (!$profesional) {
                    $response['value'] = $profesional_id;
                    $response['message'] = "Profesional no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dNombre = $em->getRepository("App:Documento")->documentoNombre($nombre, $profesional_id);
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del documento existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $servicio_tipo_documento = $em->getRepository("App:ServicioTipoDocumento")->findOneBy(array(
                'tipoDocumento' => $tipo_documento_id,
                'servicio'      => $profesional->getServicio()->getId()
            ));
            if (!$servicio_tipo_documento) {
                $response['value'] = $tipo_documento_id;
                $response['message'] = "Tipo de documento no requerido para el servicio que presta el profesional";
                return $this->JsonResponseBadRequest($response);
            }

            $documento_db = $em->getRepository("App:Documento")->findOneBy(array(
                'tipoDocumento' => $tipo_documento_id,
                'profesional'   => $profesional_id
            ));
            if ($documento_db) {
                $response['value'] = $tipo_documento_id;
                $response['message'] = "Ya existe el registro de documento con tipo_documento = $tipo_documento_id y profesional = $profesional_id";
                return $this->JsonResponseBadRequest($response);
            }

            if ($tipo_documento->getRequiereCopia() && trim($copia) == false) {
                $response['value'] = $copia;
                $response['message'] = "Por favor introduzca la copia del documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dir_uploads = $this->getParameter('dir_uploads') . $copia;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $copia;
                    $response['message'] = "Archivo especificado en copia no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if ($tipo_documento->getTipoVencimiento() == 1) {
                if (is_null($fechaVencimiento) || $fechaVencimiento == '') {
                    $response['value'] = $fechaVencimiento;
                    $response['message'] = "Debe indicar la fecha de vencimiento";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $d = \DateTime::createFromFormat("Y-m-d", $fechaVencimiento);
                    if (!($d && $d->format("Y-m-d") === $fechaVencimiento)) {
                        $response['value'] = $fechaVencimiento;
                        $response['message'] = "La fecha de vencimiento debe estar en formato AAAA-MM-DD";
                        return $this->JsonResponseBadRequest($response);
                    } else {
                        if ($fechaVencimiento <= date('Y-m-d')) {
                            $response['value'] = $fechaVencimiento;
                            $response['message'] = "La fecha de vencimiento debe ser futura";
                            return $this->JsonResponseBadRequest($response);
                        }
                        $fechaVencimiento = new \DateTime($fechaVencimiento);
                    }
                }
            }

            if ($tipo_documento->getTipoVencimiento() == 2) {
                $fechaVencimiento = new \DateTime();
                $fechaVencimiento->modify('+' . $tipo_documento->getPeriodicidad() . ' month');
            }

            $documento = new Documento();
            $documento->setProfesional($profesional);
            $documento->setNombre($nombre);
            $documento->setRuta($ruta);
            $documento->setTipoDocumento($tipo_documento);
            $documento->setCopia($tipo_documento->getRequiereCopia() ? $copia : null);
            $documento->setFechaVencimiento($tipo_documento->getTipoVencimiento() != 3 ? $fechaVencimiento : null);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($documento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($documento);
            $em->flush();

            // Se crea el subdirectorio para los archivos del documento
            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $documento->getRuta();
            $dir_uploads = $this->getParameter('dir_uploads');
            $dir = $dir_uploads . 'documentos/' . $documento->getId() . '/';

            if (isset($archivo)) {
                // Guardar el documento
                $dir_uploads = $this->getParameter('dir_uploads');
                $dir = $dir_uploads . 'documentos/' . $documento->getId() . '/';
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir, 750, true);
                }
            }

            $msg = "";
            if (!file_exists($dir_uploads) && !is_dir($dir_uploads)) {
                $msg .= " Sin embargo, el archivo no se almaceno. Asegúrese de que exista el siguiente directorio con permisos de lectura y escritura: " . $dir_uploads;
            } else {
                if (!is_null($archivo)) {                    

                    $nuevo_nombre = $nombre;

                    $ifp = fopen($dir . $nuevo_nombre, 'wb');
                    $data = explode(',', $archivo);
                    if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);

                        $documento->setRuta('documentos/' . $documento->getId() . '/' . $nuevo_nombre);

                        $em->persist($documento);
                        $em->flush();
                    }
                }
            }


            /*if (!file_exists($dir) && !is_dir($dir)) {

                mkdir($dir, 750, true);

                // Mover el archivo a su correspondiente directorio ID
                $dir_uploads_old = $this->getParameter('dir_uploads') . $documento->getRuta();
                if ($documento->getRuta() && file_exists($dir_uploads_old)) {
                    $filename = basename($dir_uploads_old);
                    rename($dir_uploads_old, $dir . $filename);
                    $documento->setRuta('documentos/' . $documento->getId() . '/' . $filename);
                    $em->persist($documento);
                    $em->flush();
                }

                /*$dir_uploads_old = $this->getParameter('dir_uploads') . $documento->getCopia();
                if ($documento->getCopia() && file_exists($dir_uploads_old)) {
                    $filename = basename($dir_uploads_old);
                    rename($dir_uploads_old, $dir . $filename);
                    $documento->setCopia('documentos/' . $documento->getId() . '/' . $filename);
                    $em->persist($documento);
                    $em->flush();
                }
            }*/

            // Se verifica si el profesional subió todos sus documentos para activarlo
            $activar = 1;
            $stds = $em->getRepository("App:ServicioTipoDocumento")->findByServicio($profesional->getServicio()->getId());
            foreach ($stds as $std) {
                $documento_db = $em->getRepository("App:Documento")->findOneBy(array(
                    'tipoDocumento' => $std->getTipoDocumento()->getId(),
                    'profesional'   => $profesional_id
                ));
                if (!$documento_db) {
                    $activar = 0;
                    break;
                }
            }
            if ($activar) {
                $profesional->setEstatus(1);
                $profesional->setUpdatedAt(new \DateTime('now'));
                $em->persist($profesional);
                $em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($documento, 201);
    }

    /**
     * obtener la lista de los documentos de un profesional
     * @Rest\Get("/documentosProfesional/{id}", name="documentos_profesional_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los documentos de un profesional",
     *     @Model(type=Documento::class)
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los Documentos"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del profesional"
     * )
     *
     * @SWG\Tag(name="Documentos")
     */
    public function getDocumentosProfesionalAction(Request $request, $id)
    {
        try {
            $profesionalActivo = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesionalActivo || is_null($profesionalActivo)) {
                return $this->JsonResponseNotFound();
            }

            $records = $this->em->getRepository("App:Documento")->findBy([
                'profesional' => $profesionalActivo->getId(),
                'eliminado' => 0
            ]);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * actualizar la información de un documento
     * @Rest\Put("/{id}", name="documento_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del documento fue actualizada satisfactoriamente.",
     *     @Model(type=Documento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del documento."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID del documento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del documento",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="Ruta del documento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="copia",
     *     in="body",
     *     type="string",
     *     description="Copia del documento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaVencimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de vencimiento del documento en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="archivo",
     *     in="body",
     *     type="string",
     *     description="documento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="Documentos")
     */
    public function editDocumentoAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $documento = $this->getDoctrine()->getManager()->getRepository("App:Documento")->findDocumentoActivo($id);
            if (!$documento || is_null($documento)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $ruta = $request->request->get('ruta', null);
            $copia = $request->request->get('copia', null);
            $fechaVencimiento = $request->request->get("fechaVencimiento", null);
            $archivo = $request->request->get("archivo", null);

            $modificar = false;
            $borrar_archivo = false;
            $borrar_copia = false;

            if (trim($nombre) && !is_null($nombre) && $documento->getNombre() != $nombre) {
                $dNombre = $this->em->getRepository("App:Documento")->documentoNombre($nombre, $documento->getProfesional()->getId(), $id);
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del documento existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $documento->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (!is_null($ruta) && $ruta != '') {
                $dir_uploads = $this->getParameter('dir_uploads') . $documento->getRuta();
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $dir_uploads;
                    $response['message'] = "Archivo especificado en ruta no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $documento->getRuta();
                    if (file_exists($dir_uploads)) {
                        $borrar_archivo = true;
                    }
                    $modificar = true;
                }
            }

            if ($documento->getTipoDocumento()->getRequiereCopia() && !is_null($copia) && $copia != '' && $documento->getCopia() != $copia) {
                $dir_uploads_copia = $this->getParameter('dir_uploads') . $copia;
                if (!file_exists($dir_uploads_copia)) {
                    $response['value'] = $copia;
                    $response['message'] = "Archivo especificado en copia no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads_copia = $this->getParameter('dir_uploads') . $documento->getCopia();
                    if (file_exists($dir_uploads_copia)) {
                        $borrar_copia = true;
                    }
                    $documento->setCopia($copia);
                    $modificar = true;
                }
            }

            if ($documento->getTipoDocumento()->getTipoVencimiento() == 1 && !is_null($fechaVencimiento) && $fechaVencimiento != '') {
                $d = \DateTime::createFromFormat("Y-m-d", $fechaVencimiento);
                if (!($d && $d->format("Y-m-d") === $fechaVencimiento)) {
                    $response['value'] = $fechaVencimiento;
                    $response['message'] = "La fecha de vencimiento debe estar en formato AAAA-MM-DD";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($fechaVencimiento <= date('Y-m-d')) {
                        $response['value'] = $fechaVencimiento;
                        $response['message'] = "La fecha de vencimiento debe ser futura";
                        return $this->JsonResponseBadRequest($response);
                    }
                    if (!$documento->getFechaVencimiento() || $documento->getFechaVencimiento()->format('Y-m-d') != $fechaVencimiento) {
                        $fechaVencimiento = new \DateTime($fechaVencimiento);
                        $documento->setFechaVencimiento($fechaVencimiento);
                        $modificar = true;
                    }
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($documento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {

                if ($borrar_archivo && !is_dir($dir_uploads)) {
                    unlink($dir_uploads);

                    $ext = 'pdf';
                    $dir = $this->getParameter('dir_uploads') . 'documentos/' . $documento->getId() . '/';
                    $nuevo_nombre = $nombre . '.' . $ext;

                    $ifp = fopen($dir . $nuevo_nombre, 'wb');
                    $data = explode(',', $archivo);

                    if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);

                        $documento->setRuta('documentos/' . $documento->getId() . '/' . $nuevo_nombre);
                    }
                }

                if ($borrar_copia && !is_dir($dir_uploads_copia)) {
                    unlink($dir_uploads_copia);
                }

                $this->em->persist($documento);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($documento, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un documento dado el ID
     * @Rest\Delete("/{id}", name="documento_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El documento fue eliminado exitosamente"
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Documento")
     *
     * @SWG\Tag(name="Documentos")
     */
    public function deleteDocumentoAction(Request $request, $id)
    {
        try {
            $documento = $this->getDoctrine()->getManager()->getRepository("App:Documento")->findDocumentoActivo($id);
            if (!$documento || is_null($documento)) {
                return $this->JsonResponseNotFound();
            }

            $documento->setEliminado(true);
            $documento->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($documento);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * renovar un documento
     * @Rest\Put("/documentosRenovar/{id}", name="documento_renovar")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del documento fue actualizada satisfactoriamente.",
     *     @Model(type=Documento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del documento."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID del documento"
     * )
     *
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="Ruta del documento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="copia",
     *     in="body",
     *     type="string",
     *     description="Copia del documento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="fechaVencimiento",
     *     in="body",
     *     type="string",
     *     description="Fecha de vencimiento del documento en formato AAAA-MM-DD",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Documentos")
     */
    public function renovarDocumentoAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Sin parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $documento = $this->getDoctrine()->getManager()->getRepository("App:Documento")->findDocumentoActivo($id);
            if (!$documento || is_null($documento)) {
                return $this->JsonResponseNotFound();
            }

            if (!$documento->getVencido()) {
                $response['value'] = $id;
                $response['message'] = "Documento aún no está vencido";
                return $this->JsonResponseBadRequest($response);
            }

            $ruta = $request->request->get('ruta', null);
            $copia = $request->request->get('copia', null);
            $fechaVencimiento = $request->request->get("fechaVencimiento", null);

            $borrar_archivo = false;
            $borrar_copia = false;

            if (trim($ruta) == false) {
                $response['value'] = $ruta;
                $response['message'] = "Por favor introduzca la ruta del documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dir_uploads = $this->getParameter('dir_uploads') . $ruta;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $ruta;
                    $response['message'] = "Archivo especificado en ruta no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $documento->getRuta();
                    if (file_exists($dir_uploads) && $documento->getRuta() != $ruta) {
                        $borrar_archivo = true;
                    }
                    $documento->setRuta($ruta);
                }
            }

            if ($documento->getTipoDocumento()->getRequiereCopia() && trim($copia) == false) {
                $response['value'] = $copia;
                $response['message'] = "Por favor introduzca la copia del documento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dir_uploads_copia = $this->getParameter('dir_uploads') . $copia;
                if (!file_exists($dir_uploads_copia)) {
                    $response['value'] = $copia;
                    $response['message'] = "Archivo especificado en copia no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads_copia = $this->getParameter('dir_uploads') . $documento->getCopia();
                    if (file_exists($dir_uploads_copia) && $documento->getCopia() != $copia) {
                        $borrar_copia = true;
                    }
                    $documento->setCopia($copia);
                }
            }

            if ($documento->getTipoDocumento()->getTipoVencimiento() == 1) {
                if (is_null($fechaVencimiento) || $fechaVencimiento == '') {
                    $response['value'] = $fechaVencimiento;
                    $response['message'] = "Debe indicar la fecha de vencimiento";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $d = \DateTime::createFromFormat("Y-m-d", $fechaVencimiento);
                    if (!($d && $d->format("Y-m-d") === $fechaVencimiento)) {
                        $response['value'] = $fechaVencimiento;
                        $response['message'] = "La fecha de vencimiento debe estar en formato AAAA-MM-DD";
                        return $this->JsonResponseBadRequest($response);
                    } else {
                        if ($fechaVencimiento <= date('Y-m-d')) {
                            $response['value'] = $fechaVencimiento;
                            $response['message'] = "La fecha de vencimiento debe ser futura";
                            return $this->JsonResponseBadRequest($response);
                        }
                        $fechaVencimiento = new \DateTime($fechaVencimiento);
                        $documento->setFechaVencimiento($fechaVencimiento);
                    }
                }
            }

            if ($documento->getTipoDocumento()->getTipoVencimiento() == 2) {
                $fechaVencimiento = new \DateTime();
                $fechaVencimiento->modify('+' . $documento->getTipoDocumento()->getPeriodicidad() . ' month');
                $documento->setFechaVencimiento($fechaVencimiento);
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($documento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $documento->setVencido(0);
            $this->em->persist($documento);
            $this->em->flush();

            if ($borrar_archivo && !is_dir($dir_uploads)) {
                unlink($dir_uploads);
            }

            if ($borrar_copia && !is_dir($dir_uploads_copia)) {
                unlink($dir_uploads_copia);
            }

            // Se verifica si el profesional renovó todos sus documentos para activarlo
            $activar = 1;
            $stds = $this->em->getRepository("App:ServicioTipoDocumento")->findByServicio($documento->getProfesional()->getServicio()->getId());
            foreach ($stds as $std) {
                $documento_db = $this->em->getRepository("App:Documento")->findOneBy(array(
                    'tipoDocumento' => $std->getTipoDocumento()->getId(),
                    'profesional'   => $documento->getProfesional()->getId(),
                    'vencido'       => false
                ));
                if (!$documento_db) {
                    $activar = 0;
                    break;
                }
            }
            if ($activar) {
                $profesional = $documento->getProfesional();
                $profesional->setEstatus(1);
                $profesional->setUpdatedAt(new \DateTime('now'));
                $this->em->persist($profesional);
                $this->em->flush();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($documento, 200, "¡Documento renovado con éxito!");
    }
}
