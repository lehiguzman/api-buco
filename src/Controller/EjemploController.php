<?php

namespace App\Controller;

use App\Controller\BaseController as BaseAPIController;
use App\Entity\Ejemplo;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

// Servicio PayUs
use App\Service\PayUsServicio;

/**
 * Ejemplo Controller
 *
 * @Route("/api/v1/ejemplos")
 * @IsGranted("ROLE_ADMIN")
 */
class EjemploController extends BaseAPIController
{
    /**
     * Lista todos los Ejemplos.
     * @Rest\Get("", name="ejemplo_list_list")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Lista todos los Ejemplos.",
     *     @Model(type=Ejemplo::class)
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ocurrió un error en el servidor al procesar esta solicitud."
     * )
     *
     * @SWG\Parameter(name="sort", in="path", type="string", description="Ordenamiento: asc o desc.")
     * @SWG\Parameter(name="field", in="path", type="string", description="Campo de ordenamiento: id, campo1, campo2.")
     * @SWG\Parameter(name="limit", in="path", type="string", description="Cantidad de registros a retornar.")
     * @SWG\Parameter(name="search", in="path", type="string", description="Valor a buscar en campos: campo1, campo2.")
     *
     * @SWG\Tag(name="Ejemplos")
     */
    public function listAction(Request $request)
    {
        try {
            $params = $request->query->all();
            // Campos con los que se podra ordenar
            $params['fields'] = ['id', 'campo1', 'campo2'];
            // Procesa los parámetros para el query
            $params = $this->QueryProcessor($params);

            $records = $this->em->getRepository("App:Ejemplo")->findEjemplos($params);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($records);
    }

    /**
     * Registra un Ejemplo en el sistema.
     * @Rest\Post("", name="ejemplo_add")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Ejemplo registrado satisfactoriamente.",
     *     @Model(type=Ejemplo::class)
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
     *     description="Campo1 y Campo2 del Ejemplo.",
     *     @SWG\Schema(
     *         @SWG\Property(property="campo1", type="string"),
     *         @SWG\Property(property="campo2", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Ejemplos")
     */
    public function createAction(Request $request)
    {
        try {
            $campo1 = $request->request->get("campo1", null);
            $campo2 = $request->request->get("campo2", null);

            if (trim($campo1) == false) {
                $response['value'] = $campo1;
                $response['info'] = "Por favor introduzca un campo1";

                return $this->JsonResponseBadRequest($response);
            }

            if (trim($campo2) == false) {
                $response['value'] = $campo2;
                $response['info'] = "Por favor introduzca un campo2";

                return $this->JsonResponseBadRequest($response);
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $entity = new Ejemplo();
            $entity->setCampo1($campo1);
            $entity->setCampo2($campo2);

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($entity);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            $this->em->persist($entity);
            $this->em->flush();

            // Confirma Transacciones a Base de Datos
            $this->em->getConnection()->commit();
        } catch (Exception $ex) {
            // Cancela Transacciones a Base de Datos
            $this->em->getConnection()->rollback();
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 201);
    }

    /**
     * Obtiene los datos del Ejemplo.
     * @Rest\Get("/{id}", name="ejemplo_details")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene los datos del Ejemplo.",
     *     @Model(type=Ejemplo::class)
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Ejemplo")
     *
     *
     * @SWG\Tag(name="Ejemplos")
     */
    public function detailAction($id)
    {
        try {
            $entity = $this->em->getRepository("App:Ejemplo")->findEjemploActivo($id);
            if (!$entity || is_null($entity)) {
                return $this->JsonResponseNotFound();
            }
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity);
    }

    /**
     * Actualiza los datos de un Ejemplo.
     * @Rest\Put("/{id}", name="ejemplo_edit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Ejemplo actualizado satisfactoriamente.",
     *     @Model(type=Ejemplo::class)
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Ejemplo")
     * 
     * @SWG\Parameter(
     *     name="Parámetros",
     *     in="body",
     *     required=true,
     *     description="Campo1 y Campo2 del Ejemplo.",
     *     @SWG\Schema(
     *         @SWG\Property(property="campo1", type="string"),
     *         @SWG\Property(property="campo2", type="string"),
     *     )
     * )
     *
     *
     * @SWG\Tag(name="Ejemplos")
     */
    public function updateAction(Request $request, $id)
    {
        try {
            if ($request->request->count() == 0) {
                $response['value'] = NULL;
                $response['message'] = "Debe enviar al menos un parámetros a modificar";

                return $this->JsonResponseBadRequest($response);
            }

            $entity = $this->em->getRepository("App:Ejemplo")->findEjemploActivo($id);
            if (!$entity || is_null($entity)) {
                return $this->JsonResponseNotFound();
            }

            // Comienza Transacciones a Base de Datos
            $this->em->getConnection()->beginTransaction();

            $campo1 = $request->request->get("campo1", null);
            $campo2 = $request->request->get("campo2", null);

            $modificar = false;
            if (trim($campo1) && !is_null($campo1) && $entity->getCampo1() != $campo1) {
                $entity->setCampo1($campo1);
                $modificar = true;
            }

            if (trim($campo2) && !is_null($campo2) && $entity->getCampo2() != $campo2) {
                $entity->setCampo2($campo2);
                $modificar = true;
            }

            // Verificar datos de la Entidad
            $errors = $this->validator->validate($entity);
            if (count($errors) > 0) {
                return $this->JsonResponseError($errors, 'validator');
            }

            if ($modificar) {
                $this->em->persist($entity);
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

        return $this->JsonResponseSuccess($entity, 200, "¡Registro modificado con éxito!");
    }

    /**
     * Elimina un Ejemplo.
     * @Rest\Delete("/{id}", name="ejemplo_remove")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Ejemplo eliminado satisfactoriamente.",
     *     @Model(type=Ejemplo::class)
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
     * @SWG\Parameter(name="id", in="path", type="integer", description="Identificador del Ejemplo")
     *
     *
     * @SWG\Tag(name="Ejemplos")
     */
    public function deleteAction($id)
    {
        try {
            $entity = $this->em->getRepository("App:Ejemplo")->findEjemploActivo($id);
            if (!$entity || is_null($entity)) {
                return $this->JsonResponseNotFound();
            }

            // eliminación lógica
            $entity->setFechaEliminado(new \DateTime('now'));
            $entity->setEliminado(true);
            $entity->setEstado(0);

            // eliminación física
            // $this->em->remove($entity);

            $this->em->persist($entity);
            $this->em->flush();
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($entity, 200, "¡Registro eliminado con éxito!");
    }

    /**
     * endpoint para pruebas.
     * @Rest\Post("/test/pruebas", name="ejemplo_test")
     */
    public function testPruebasAction(PayUsServicio $payusServ)
    {
        try {
            $result = [null];
            // ...

            $datos = [
                'cliente' => 25,
                'numero' => "5555555555555555",
                'mesanio' => "1222",
                'cvv' => 123,
            ];
            $result[] = $payusServ->registrarTarjeta($datos);

            $datos = [
                'orden' => 123,
                'montoTotal' => 999.99,
                'cardToken' => "zL3B8MAQ9lhxINaAWkAphF3naXMfIeu+XL2HvUuxWZvd+me-9BVuoV0kAJlqPE+LMLA5wgp0V-CtKf6MPDW+QQ==",
            ];
            $result[] = $payusServ->procesarPago($datos);
        } catch (Exception $ex) {
            return $this->JsonResponseError($ex, 'exception');
        }

        return $this->JsonResponseSuccess($result, 200, "¡Prueba exitosa!");
    }

    /**
     * endpoint para pruebas de correo.
     * @Rest\Post("/test/email", name="ejemplo_test_email")
     */
    public function testCorreoAction()
    {
        /** INICIO - ENVIO DE CORREOS */
        try {
            $resp = "Exitoso";

            $confCorreo = [
                'asunto' => "Profesional Aprobado",
                'correos' => ['jarcia@strappinc.net'],
                'plantilla' => "email/profesional_aprobado.html.twig",
                'datos' => [
                    'nombre' => "Juan Arcia"
                ]
            ];

            $resp = $this->enviarCorreo->enviar($confCorreo);
        } catch (Exception $ex) {
            // NO RETORAR NADA 
        }
        /** FIN - ENVIO DE CORREOS */

        return $this->JsonResponse($resp);
    }
}
