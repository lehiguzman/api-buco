<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Direccion;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DireccionController
 *
 * @Route("/api/v1")
 */
class DireccionController extends BaseAPIController
{
    /**
     * Lista las direcciones
     * @Rest\Get("/direcciones", name="direccion_list_all")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene todas las direcciones",
     *     @Model(type=Direccion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo todas las direcciones"
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id o nombre.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: nombre.")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function getAllDireccionAction(Request $request)
    {
        try {

            $params = $request->query->all();
            // Campos con los que se podrá ordenar
            $params['fields'] = ['id', 'nombre'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->em->getRepository("App:Direccion")->findDirecciones($params);

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
     * agregar una nueva dirección
     * @Rest\Post("/direcciones", name="direccion_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Dirección agregada exitosamente",
     *     @Model(type=Direccion::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de agregar una nueva dirección"
     * )
     *
     * @SWG\Parameter(
     *     name="user_id",
     *     in="body",
     *     type="integer",
     *     description="Id del cliente al que pertenece la dirección",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de dirección. Valores permitidos: 1: Casa, 2: Trabajo, 3: Otro.",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="direccion",
     *     in="body",
     *     type="string",
     *     description="Dirección",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="float",
     *     description="Latitud de la dirección",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="float",
     *     description="Longitud de la dirección",
     *     schema={},
     *     required=true
     * )
     *
     * @SWG\Parameter(
     *     name="residencia",
     *     in="body",
     *     type="string",
     *     description="Residencia",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="pisoNumero",
     *     in="body",
     *     type="string",
     *     description="Número de piso",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="telefono",
     *     in="body",
     *     type="string",
     *     description="Teléfono del cliente",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="telefonoMovil",
     *     in="body",
     *     type="string",
     *     description="Teléfono Movil del cliente",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="instruccion",
     *     in="body",
     *     type="string",
     *     description="Instrucción o referencia",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function addDireccionAction(Request $request)
    {
        $em = $this->em;

        try {

            $user_id = $request->request->get('user_id', null);
            $tipo = $request->request->get("tipo", null);
            $address = $request->request->get("direccion", null);
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);
            $residencia = $request->request->get("residencia", null);
            $pisoNumero = $request->request->get("pisoNumero", null);
            $telefono = $request->request->get("telefono", null);
            $telefonoMovil = $request->request->get("telefonoMovil", null);
            $instruccion = $request->request->get("instruccion", null);

            if (is_null($user_id)) {
                $response['value'] = $user_id;
                $response['message'] = "Por favor introduzca el usuario";
                return $this->JsonResponseBadRequest($response);
            } else {
                $user = $em->getRepository("App:User")->findUserActivo($user_id);
                if (!$user) {
                    $response['value'] = $user_id;
                    $response['message'] = "Usuario no encontrado";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($tipo) == false) {
                $response['value'] = $tipo;
                $response['message'] = "Por favor introduzca una tipo de dirección";

                return $this->JsonResponseBadRequest($response);
            } else {
                if (!($tipo == "1" || $tipo == "2" || $tipo == "3")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo de dirección: 1: Casa, 2: Trabajo, 3: Otro";
                    return $this->JsonResponseBadRequest($response);
                }
            }

            if (trim($address) == false) {
                $response['value'] = $address;
                $response['message'] = "Por favor introduzca una dirección";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($latitud) == false) {
                $response['value'] = $latitud;
                $response['message'] = "Por favor introduzca latitud";
                return $this->JsonResponseBadRequest($response);
            }

            if (trim($longitud) == false) {
                $response['value'] = $longitud;
                $response['message'] = "Por favor introduzca una longitud";
                return $this->JsonResponseBadRequest($response);
            }

            $direccion = new Direccion();
            $direccion->setUser($user);
            $direccion->setTipo(intval($tipo));
            $direccion->setDireccion($address);
            $direccion->setLatitud(floatval($latitud));
            $direccion->setLongitud(floatval($longitud));
            $direccion->setResidencia($residencia);
            $direccion->setPisoNumero($pisoNumero);
            $direccion->setTelefono($telefono);
            $direccion->setTelefonoMovil($telefonoMovil);
            $direccion->setInstruccion($instruccion);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($direccion);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $em->persist($direccion);
            $em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direccion, 201);
    }

    /**
     * obtener la información de una dirección dado el ID
     * @Rest\Get("/direcciones/{id}", name="direccion_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la dirección basado en parámetro ID.",
     *     @Model(type=Direccion::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     * 
     * @SWG\Response(
     *     response=400,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la direccion"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function getDireccionAction(Request $request, $id)
    {
        try {
            $direccion = $this->em->getRepository("App:Direccion")->findDireccionActivo($id);
            if (!$direccion || is_null($direccion)) {
                return $this->JsonResponseNotFound();
            }
            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $direccion->getUser()->getFoto();
            $dir_uploads = $this->getParameter('dir_uploads') . $direccion->getUser()->getFoto();
            if ($direccion->getUser()->getFoto() && file_exists($dir_uploads)) {
                $direccion->getUser()->setFoto($uploads);
            } else {
                $direccion->getUser()->setFoto('');
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direccion);
    }

    /**
     * actualizar la información de una dirección
     * @Rest\Put("/direcciones/{id}", name="direccion_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La dirección fue actualizada exitosamente."
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error tratando de actualizar la información de la dirección."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la dirección"
     * )
     *
     * @SWG\Parameter(
     *     name="tipo",
     *     in="body",
     *     type="integer",
     *     description="Tipo de dirección. Valores permitidos: 1: Casa, 2: Trabajo, 3: Otro.",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="direccion",
     *     in="body",
     *     type="string",
     *     description="Dirección",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="latitud",
     *     in="body",
     *     type="float",
     *     description="Latitud de la dirección",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="longitud",
     *     in="body",
     *     type="float",
     *     description="Longitud de la dirección",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="residencia",
     *     in="body",
     *     type="string",
     *     description="Residencia",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="pisoNumero",
     *     in="body",
     *     type="string",
     *     description="Número de piso",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="telefono",
     *     in="body",
     *     type="string",
     *     description="Teléfono del cliente",
     *     schema={},
     *     required=false
     * )
     * 
     * @SWG\Parameter(
     *     name="telefonoMovil",
     *     in="body",
     *     type="string",
     *     description="Teléfono Movil del cliente",
     *     schema={},
     *     required=false
     * )
     *
     * @SWG\Parameter(
     *     name="instruccion",
     *     in="body",
     *     type="string",
     *     description="Instrucción o referencia",
     *     schema={},
     *     required=false
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function editDireccionAction(Request $request, $id)
    {
        try {

            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetro a modificar";
                return $this->JsonResponseBadRequest($response);
            }

            $direccion = $this->em->getRepository("App:Direccion")->findDireccionActivo($id);
            if (!$direccion || is_null($direccion)) {
                return $this->JsonResponseNotFound();
            }

            $tipo = $request->request->get("tipo", null);
            $address = $request->request->get("direccion", null);
            $latitud = $request->request->get("latitud", null);
            $longitud = $request->request->get("longitud", null);
            $residencia = $request->request->get("residencia", null);
            $pisoNumero = $request->request->get("pisoNumero", null);
            $telefono = $request->request->get("telefono", null);
            $telefonoMovil = $request->request->get("telefonoMovil", null);
            $instruccion = $request->request->get("instruccion", null);

            $modificar = false;

            if (!is_null($tipo)) {
                if (!($tipo == "1" || $tipo == "2" || $tipo == "3")) {
                    $response['value'] = $tipo;
                    $response['message'] = "Valores permitidos del tipo de dirección: 1: Casa, 2: Trabajo, 3: Otro";
                    return $this->JsonResponseBadRequest($response);
                } else {
                    if ($direccion->getTipo() != $tipo) {
                        $direccion->setTipo($tipo);
                        $modificar = true;
                    }
                }
            }

            if (trim($address) && !is_null($address) && $direccion->getDireccion() != $address) {
                $direccion->setDireccion($address);
                $modificar = true;
            }

            if (trim($latitud) && !is_null($latitud) && $direccion->getLatitud() != floatval($latitud)) {
                $direccion->setLatitud(floatval($latitud));
                $modificar = true;
            }

            if (trim($longitud) && !is_null($longitud) && $direccion->getLongitud() != floatval($longitud)) {
                $direccion->setLongitud(floatval($longitud));
                $modificar = true;
            }

            if (trim($residencia) && !is_null($residencia) && $direccion->getResidencia() != $residencia) {
                $direccion->setResidencia($residencia);
                $modificar = true;
            }

            if (trim($pisoNumero) && !is_null($pisoNumero) && $direccion->getPisoNumero() != $pisoNumero) {
                $direccion->setPisoNumero($pisoNumero);
                $modificar = true;
            }

            if (trim($telefono) && !is_null($telefono) && $direccion->getTelefono() != $telefono) {
                $direccion->setTelefono($telefono);
                $modificar = true;
            }

            if (trim($telefonoMovil) && !is_null($telefonoMovil) && $direccion->getTelefonoMovil() != $telefonoMovil) {
                $direccion->setTelefonoMovil($telefonoMovil);
                $modificar = true;
            }

            if (trim($instruccion) && !is_null($instruccion) && $direccion->getInstruccion() != $instruccion) {
                $direccion->setInstruccion($instruccion);
                $modificar = true;
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($direccion);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($direccion);
                $this->em->flush();
            } else {
                return $this->JsonResponseSuccess(NULL, 200, "¡Registro sin alterar!");
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direccion, 200, "¡Registro modificado con éxito!");
    }

    /**
     * eliminar una dirección dado el ID
     * @Rest\Delete("/direcciones/{id}", name="direccion_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="La dirección fue eliminada exitosamente"
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
     *     description="Id de la dirección"
     * )
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function deleteDireccionAction(Request $request, $id)
    {
        try {

            $direccion = $this->em->getRepository("App:Direccion")->findDireccionActivo($id);
            if (!$direccion || is_null($direccion)) {
                return $this->JsonResponseNotFound();
            }

            $direccion->setEliminado(true);
            $direccion->setFechaEliminado(new \DateTime('now'));
            $this->em->persist($direccion);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess(NULL, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * obtener las direcciones dado el clienteID
     * @Rest\Get("/direcciones/cliente/{clienteId}", name="direccion_list_cliente")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene las direcciones basado en el parámetro clienteID.",
     *     @Model(type=Direccion::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Registro no encontrado."
     * )
     * 
     * @SWG\Response(
     *     response=400,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="clienteId", in="path", type="string",  description="Id del cliente"),
     * @SWG\Parameter(name="predeterminada", in="path", type="integer", description="1: dirección predeterminada -- 0:lista de direcciones")
     *
     * @IsGranted("ROLE_USER")
     * @SWG\Tag(name="Direcciones")
     */
    public function getDireccionesClienteAction(Request $request, $clienteId)
    {
        try {
            $user = $this->em->getRepository("App:User")->findUserActivo($clienteId);
            if (!$user || is_null($user)) {
                return $this->JsonResponseNotFound();
            }

            $params = $request->query->all(); 

            if (isset($params['predeterminada']) && intval($params['predeterminada']) === 1) {
                $dirrPredeterminada = $this->em->getRepository("App:Direccion")->findOneBy([
                    'user'           => $user->getId(),
                    'sistemaTipo'    => 1,
                    'predeterminada' => true,
                    'eliminado'      => false
                ]);
                if ($dirrPredeterminada) {
                    return $this->JsonResponseSuccess($dirrPredeterminada);
                }   
            }

            $direcciones = $this->em->getRepository("App:Direccion")->findDireccionUser($clienteId);
            foreach ($direcciones as $direccion) {
                $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/' . $direccion->getUser()->getFoto();
                $dir_uploads = $this->getParameter('dir_uploads') . $direccion->getUser()->getFoto();
                if ($direccion->getUser()->getFoto() && file_exists($dir_uploads)) {
                    $direccion->getUser()->setFoto($uploads);
                } else {
                    $direccion->getUser()->setFoto('');
                }
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($direcciones);
    }
}
