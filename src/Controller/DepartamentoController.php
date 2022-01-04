<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Departamento;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class DepartamentoController
 *
 * @Route("/api/v1")
 * @IsGranted("ROLE_USER")
 */
class DepartamentoController extends BaseAPIController
{
    // Departamento URI's

    /**
     * Lista los departamentos
     * @Rest\Get("/departamentos", name="departamento_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los departamentos",
     *     @Model(type=Departamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los departamentos"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @SWG\Tag(name="Departamentos")
     */
    public function getAllDepartamentoAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->getDoctrine()->getManager()->getRepository("App:Departamento")->findDepartamentos($params);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getIcono();
                if ($entity->getIcono() && file_exists($dir_uploads)) {
                    $entity->setIcono($uploads);
                } else {
                    $entity->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Lista los departamentos de determinado tipo de sistema
     * @Rest\Get("/departamentos/sistema/{id}", name="departamento_sistema_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todos los departamentos de un determinado sistema",
     *     @Model(type=Departamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todos los departamentos"
     * )
     *
     * @SWG\Tag(name="Departamentos")
     */
    public function getAllDepartamentoSistemaAction(Request $request, $id)
    {
        try {

            $records = $this->getDoctrine()->getManager()->getRepository("App:Departamento")->findDepartamentosSistema($id);

            foreach ($records as $entity) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $entity->getIcono();
                $dir_uploads = $this->getParameter('dir_uploads') . $entity->getIcono();
                if ($entity->getIcono() && file_exists($dir_uploads)) {
                    $entity->setIcono($uploads);
                } else {
                    $entity->setIcono('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }



    /**
     * obtener la información de un departamento dado el ID
     * @Rest\Get("/departamentos/{id}", name="departamento_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene el departamento basado en parámetro ID.",
     *     @Model(type=Departamento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El departamento ID"
     * )
     *
     *
     * @SWG\Tag(name="Departamentos")
     */
    public function getDepartamentoAction(Request $request, $id)
    {
        try {
            $departamento = $this->getDoctrine()->getManager()->getRepository("App:Departamento")->findDepartamentoActivo($id);
            if (!$departamento || is_null($departamento)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $departamento->getIcono();
        $dir_uploads = $this->getParameter('dir_uploads') . $departamento->getIcono();
        if ($departamento->getIcono() && file_exists($dir_uploads)) {
            $departamento->setIcono($uploads);
        } else {
            $departamento->setIcono('');
        }

        return $this->JsonResponseSuccess($departamento);
    }

    /**
     * agregar un nuevo departamento
     * @Rest\Post("/departamentos", name="departamento_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Departamento agregado exitosamente",
     *     @Model(type=Departamento::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar un nuevo departamento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del departamento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción del departamento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del departamento, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=true
     * )
     * 
     * @SWG\Parameter(
     *     name="sistemaTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de sistema, valores permitidos: 1: bucoservicio, 2: bucotalento",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="icono",
     *     in="body",
     *     type="string",
     *     description="Ruta relativa del ícono, ej: departamentos/imagen.jpg",
     *     schema={},
     *     required=false
     * )
     *
     *
     * @SWG\Tag(name="Departamentos")
     */
    public function addDepartamentoAction(Request $request)
    {
        $em = $this->em;

        try {
            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $estatus = $request->request->get('estatus', 1);
            $sistemaTipo = $request->request->get('sistemaTipo', 1);
            $icono = $request->request->get('icono', null);

            if (trim($nombre) == false) {
                $response['value'] = $nombre;
                $response['message'] = "Por favor introduzca el nombre del departamento";
                return $this->JsonResponseBadRequest($response);
            } else {
                $dNombre = $em->getRepository("App:Departamento")->departamentoNombre($nombre);
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de departamento existente";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (!is_null($sistemaTipo)) {
                if (!($sistemaTipo == "1" || $sistemaTipo == "2")) {
                    $response['value'] = $sistemaTipo;
                    $response['message'] = "Valores permitidos del Tipo de sistema: 1: bucoservicio, 2: bucotalento";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            $departamento = new Departamento();
            $departamento->setNombre($nombre);
            $departamento->setDescripcion($descripcion);
            $departamento->setEstatus(intval($estatus));
            $departamento->setSistemaTipo(intval($sistemaTipo));
            $departamento->setIcono($icono);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($departamento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($departamento);
            $em->flush();

            // Se crea el subdirectorio para los archivos del departamento
            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $departamento->getIcono();
            $dir_uploads = $this->getParameter('dir_uploads');
            $dir = $dir_uploads . 'departamentos/' . $departamento->getId() . '/';

            if (!file_exists($dir) && !is_dir($dir)) {

                mkdir($dir, 750, true);

                // Mover el archivo a su correspondiente directorio ID
                $dir_uploads_old = $this->getParameter('dir_uploads') . $departamento->getIcono();
                if ($departamento->getIcono() && file_exists($dir_uploads_old)) {
                    $filename = basename($dir_uploads_old);
                    rename($dir_uploads_old, $dir . $filename);
                    $departamento->setIcono('departamentos/' . $departamento->getId() . '/' . $filename);
                    $em->persist($departamento);
                    $em->flush();
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($departamento, 201);
    }

    /**
     * actualizar la información de un departamento
     * @Rest\Put("/departamentos/{id}", name="departamento_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La información del departamento fue actualizada satisfactoriamente.",
     *     @Model(type=Departamento::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información del departamento."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="El ID del departamento"
     * )
     *
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre del departamento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="descripcion",
     *     in="body",
     *     type="string",
     *     description="Descripción del departamento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="estatus",
     *     in="body",
     *     type="integer",
     *     description="Estatus del departamento, valores permitidos: 0: Inactivo, 1: Activo",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="sistemaTipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de servicio, valores permitidos: 1: bucoservicio, 2: bucotalento",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="icono",
     *     in="body",
     *     type="string",
     *     description="Ruta relativa del ícono, ej: departamentos/imagen.jpg",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Tag(name="Departamentos")
     */
    public function editDepartamentoAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $departamento = $this->getDoctrine()->getManager()->getRepository("App:Departamento")->findDepartamentoActivo($id);
            if (!$departamento || is_null($departamento)) {
                return $this->JsonResponseNotFound();
            }

            $nombre = $request->request->get('nombre', null);
            $descripcion = $request->request->get('descripcion', null);
            $estatus = $request->request->get('estatus', null);
            $sistemaTipo = $request->request->get('sistemaTipo', null);
            $icono = $request->request->get('icono');

            $modificar = false;
            $borrar_archivo = false;

            if (trim($nombre) && !is_null($nombre) && $departamento->getNombre() != $nombre) {
                $dNombre = $this->em->getRepository("App:Departamento")->departamentoNombre($nombre, $id);
                if ($dNombre) {
                    $response['value'] = $nombre;
                    $response['message'] = "Nombre de departamento existente";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    $departamento->setNombre($nombre);
                    $modificar = true;
                }
            }

            if (trim($descripcion) && !is_null($descripcion) && $departamento->getDescripcion() != $descripcion) {
                $departamento->setDescripcion($descripcion);
                $modificar = true;
            }

            if (!is_null($estatus)) {
                if (!($estatus == "0" || $estatus == "1")) {
                    $response['value'] = $estatus;
                    $response['message'] = "Valores permitidos del estatus: 0: Inactivo, 1: Activo";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($departamento->getEstatus() != intval($estatus)) {
                        $departamento->setEstatus(intval($estatus));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($sistemaTipo)) {
                if (!($sistemaTipo == "1" || $sistemaTipo == "2")) {
                    $response['value'] = $sistemaTipo;
                    $response['message'] = "Valores permitidos del tipo de sistema: 1: bucoservicio, 2: bucotalento";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($departamento->getSistemaTipo() != intval($sistemaTipo)) {
                        $departamento->setSistemaTipo(intval($sistemaTipo));
                        $modificar = true;
                    }
                }
            }

            if (!is_null($icono) && $icono != '' && $departamento->getIcono() != $icono) {
                $dir_uploads = $this->getParameter('dir_uploads') . $icono;
                if (!file_exists($dir_uploads)) {
                    $response['value'] = $icono;
                    $response['message'] = "Archivo no encontrado";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    // Se debe borrar el archivo anterior de la carpeta en el servidor
                    $dir_uploads = $this->getParameter('dir_uploads') . $departamento->getIcono();
                    if (file_exists($dir_uploads)) {
                        $borrar_archivo = true;
                    }
                    $departamento->setIcono($icono);
                    $modificar = true;
                }
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($departamento);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {

                $this->em->persist($departamento);
                $this->em->flush();

                if ($borrar_archivo && !is_dir($dir_uploads)) {
                    unlink($dir_uploads);
                }
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($departamento, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar un departamento dado el ID
     * @Rest\Delete("/departamentos/{id}", name="departamento_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="El departamento fue eliminado exitosamente"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     *
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Departamento")
     *
     * @IsGranted("ROLE_ADMIN")
     * @SWG\Tag(name="Departamentos")
     */
    public function deleteDepartamentoAction(Request $request, $id)
    {
        try {
            $departamento = $this->getDoctrine()->getManager()->getRepository("App:Departamento")->findDepartamentoActivo($id);
            if (!$departamento || is_null($departamento)) {
                return $this->JsonResponseNotFound();
            }

            // Se debe eliminar los registros de ServicioDepartamento asociados a este departamento_id
            $sds = $this->getDoctrine()->getManager()->getRepository("App:ServicioDepartamento")->findbyDepartment($id);
            foreach ($sds as $sd) {
                $this->em->remove($sd);
                $this->em->flush();
            }

            $departamento->setEliminado(true);
            $departamento->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($departamento);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }
}
