<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\ArchivoPortafolio;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DocumentoController
 *
 * @Route("/api/v1/archivoportafolio")
 * @IsGranted("ROLE_USER")
 */
class ArchivoPortafolioController extends BaseAPIController
{
    /**
     * Lista los archivos
     * @Rest\Get("", name="archivoportafolio_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los archivos",
     *     @Model(type=ArchivoPortafolio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los archivos"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="ArchivoPortafolios")
     */
    public function getAllArchivoPortafolioAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:ArchivoPortafolio")->findArchivoPortafolios($params);

            foreach ($records as $entity) {

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getRuta();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getRuta();
                if ($entity->getRuta() && file_exists($dir_uploads)) {
                    $entity->setRuta($uploads);
                } else {
                    $entity->setRuta('');
                }                
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * agregar un archivo
     * @Rest\Post("", name="archivoportafolio_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Archivo agregado exitosamente",
     *     @Model(type=ArchivoPortafolio::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrio un error agregando un nuevo archivo"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del archivo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="Ruta del archivo",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipo_archivo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de archivo",
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
     *     name="archivo",
     *     in="body",
     *     type="string",
     *     description="archivo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="extension",
     *     in="body",
     *     type="string",
     *     description="extension del archivo",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Tag(name="ArchivoPortafolio")
     */
    public function addArchivoPortafolioAction(Request $request)
    {
        $em = $this->em;
        try {
            $nombre = $request->request->get('nombre', null);
            $ruta = $request->request->get('ruta', null);
            $tipo_archivo = $request->request->get('tipo_archivo', null);
            $profesional_id = $request->request->get('profesional', null);                        
            $archivo = $request->request->get("archivo", null);
            $extension = $request->request->get("extension", null);

            if (trim($ruta) == false) {
                $response['value'] = $ruta;
                $response['message'] = "Por favor introduzca la ruta del archivo";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dir_uploads = $this->getParameter('dir_uploads') . $ruta;
                /*if (!file_exists($dir_uploads)) {
                    $response['value'] = $ruta;
                    $response['message'] = "Archivo especificado en ruta no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }*/
            }

            if (is_null($tipo_archivo)) {
                $response['value'] = $tipo_archivo;
                $response['message'] = "Por favor introduzca el tipo archivo";
                return $this->JsonResponseBadRequest($response);
            } else {                
                if (!$tipo_archivo) {
                    $response['value'] = $tipo_archivo;
                    $response['message'] = "Tipo de archivo no encontrado";
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
                $response['message'] = "Por favor introduzca el nombre del archivo";
                return $this->JsonResponseBadRequest($response);
            } /*else {                
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del documento existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }*/

            $archivoPortafolio = new ArchivoPortafolio();
            $archivoPortafolio->setProfesional($profesional);
            $archivoPortafolio->setNombre($nombre);
            $archivoPortafolio->setRuta($ruta);
            $archivoPortafolio->setTipoArchivo($tipo_archivo);      
            
            $d = new \DateTime('now');
            $archivoPortafolio->setFechaCreacion($d);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($archivoPortafolio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($archivoPortafolio);
            $em->flush();

            // Se crea el subdirectorio para los archivos del documento
            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $archivoPortafolio->getRuta();
            $dir_uploads = $this->getParameter('dir_uploads');
            $dir = $dir_uploads . 'portafolio/archivos/' . $profesional_id . '/';

            if (isset($archivo)) {
                // Guardar el documento
                $dir_uploads = $this->getParameter('dir_uploads');
                $dir = $dir_uploads . 'portafolio/archivos/' . $profesional_id . '/';
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir, 750, true);
                }
            }

            $msg = "";
            if (!file_exists($dir_uploads) && !is_dir($dir_uploads)) {
                $msg .= " Sin embargo, el archivo no se almaceno. Asegúrese de que exista el siguiente directorio con permisos de lectura y escritura: " . $dir_uploads;
            } else {
                if (!is_null($archivo) && !is_null($extension)) {                    

                    $nuevo_nombre = $nombre . '.' . $extension;

                    $ifp = fopen($dir . $nuevo_nombre, 'wb');
                    $data = explode(',', $archivo);
                    if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);

                        $archivoPortafolio->setRuta('portafolio/archivos/' . $profesional_id . '/' . $nuevo_nombre);

                        $em->persist($archivoPortafolio);
                        $em->flush();
                    }
                }
            }            
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($archivoPortafolio, 201);
    }

    /**
     * obtener la lista de los archivos del portafolio de un profesional
     * @Rest\Get("/{id}", name="archivoportafolio_profesional_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los archivos del portafolio de un profesional",
     *     @Model(type=Documento::class)
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los archivos del portafolio de un profesional"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del profesional"
     * )
     *
     * @SWG\Tag(name="Archivos Portafolio")
     */
    public function getArchivoPortafolioProfesionalAction(Request $request, $id)
    {
        try {
            $profesionalActivo = $this->getDoctrine()->getManager()->getRepository("App:Profesional")->findProfesionalActivo($id);
            if (!$profesionalActivo || is_null($profesionalActivo)) {
                return $this->JsonResponseNotFound();
            }

            $records = $this->getDoctrine()->getManager()->getRepository("App:ArchivoPortafolio")->findArchivoPortafolioByProfesional($id);
            foreach ($records as $entity) {

                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getRuta();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getRuta();
                if ($entity->getRuta() && file_exists($dir_uploads)) {
                    $entity->setRuta($uploads);
                } else {
                    $entity->setRuta('');
                }                
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * actualizar la información de un archivo
     * @Rest\Put("/{id}", name="archivoportafolio_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del archvivo fue actualizada satisfactoriamente.",
     *     @Model(type=ArchivoPortafolio::class)
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
     *     description="El ID del archivo"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del archivo",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="ruta",
     *     in="body",
     *     type="string",
     *     description="Ruta del archivo",
     *     schema={},
     *     required=false
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
     *     name="tipo_archivo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de archivo",
     *     schema={},
     *     required=true
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
     * @SWG\Parameter(
     *     name="extension",
     *     in="body",
     *     type="string",
     *     description="extension del archivo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Tag(name="ArchivoPortafolios")
     */
    public function editArchivoPortafolioAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $archivoPortafolio = $this->getDoctrine()->getManager()->getRepository("App:ArchivoPortafolio")->findArchivoPortafolioActivo($id);
            if (!$archivoPortafolio || is_null($archivoPortafolio)) {
                return $this->JsonResponseNotFound();
            }

            $profesional_id = $request->request->get('profesional', null);
            $nombre = $request->request->get('nombre', null);
            $ruta = $request->request->get('ruta', null);            
            $archivo = $request->request->get("archivo", null);
            $tipo_archivo = $request->request->get("tipo_archivo", null);
            $extension = $request->request->get("extension", null);

            $modificar = false;
            $borrar_archivo = false;            

            if (trim($nombre) && !is_null($nombre) && $archivoPortafolio->getNombre() != $nombre) {
                /*$dNombre = $this->em->getRepository("App:Documento")->documentoNombre($nombre, $documento->getProfesional()->getId(), $id);
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre del documento existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $documento->setNombre($nombre);
                    $modificar = true;
                }*/
                $archivoPortafolio->setNombre($nombre);
                $modificar = true;
            }

            if($tipo_archivo && $archivoPortafolio->getTipoArchivo() != $tipo_archivo) {
                $archivoPortafolio->setTipoArchivo($tipo_archivo);
                $modificar = true;
            }

            if (!is_null($ruta) && $ruta != '') {
                $dir_uploads = $this->getParameter('dir_uploads') . $archivoPortafolio->getRuta();
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $dir_uploads;
                    $response['message'] = "Archivo especificado en ruta no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $archivoPortafolio->getRuta();
                    if (file_exists($dir_uploads)) {
                        $borrar_archivo = true;
                    }
                    $modificar = true;
                }
            }            

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($archivoPortafolio);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {

                if ($borrar_archivo && !is_dir($dir_uploads)) {
                    unlink($dir_uploads);
                    
                    $dir = $this->getParameter('dir_uploads') . 'portafolio/archivos/' . $profesional_id . '/';
                    $nuevo_nombre = $nombre . '.' . $extension;

                    $ifp = fopen($dir . $nuevo_nombre, 'wb');
                    $data = explode(',', $archivo);

                    if (isset($data[1]) && base64_decode($data[1], true) !== false) {

                        fwrite($ifp, base64_decode($data[1]));
                        fclose($ifp);

                        $archivoPortafolio->setRuta('portafolio/archivos/' . $profesional_id . '/' . $nuevo_nombre);
                    }
                }                

                $this->em->persist($archivoPortafolio);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($archivoPortafolio, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un archivo dado el ID
     * @Rest\Delete("/{id}", name="archivoportafolio_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El archivo fue eliminado exitosamente"
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
     * @SWG\Tag(name="ArchivoPortafolios")
     */
    public function deleteArchivoPortafolioAction(Request $request, $id)
    {
        try {
            $archivoPortafolio = $this->getDoctrine()->getManager()->getRepository("App:ArchivoPortafolio")->findArchivoPortafolioActivo($id);
            if (!$archivoPortafolio || is_null($archivoPortafolio)) {
                return $this->JsonResponseNotFound();
            }

            $archivoPortafolio->setEliminado(true);
            $archivoPortafolio->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($archivoPortafolio);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    
}
