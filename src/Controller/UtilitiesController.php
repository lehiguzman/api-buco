<?php

namespace App\Controller;

use App\Entity\Departamento;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Services\UploadHandler;

/**
 * Class UtilitiesController
 *
 * @Route("/api/v1")
 */
class UtilitiesController extends FOSRestController
{

    // Departamento URI's

    /**
     * obtener la ruta física del directorio uploads
     * @Rest\Get("/uploads", name="uploads")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la ruta física y la URL del directorio uploads/"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo la ruta física y URL del directorio uploads/"
     * )
     *
     *
     *
     * @SWG\Tag(name="Utilities")
     */
    public function getUploadsAction(Request $request): Response
    {

        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $urls = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $uploads = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/uploads/';
            $dir_uploads = $this->getParameter('dir_uploads');
            if (file_exists($dir_uploads) && is_dir($dir_uploads)) {
                $urls = array(
                    'upload_url' => $uploads,
                    'upload_dir' => $dir_uploads
                );
            } else {
                $code = 404;
                $error = true;
                $message = "Directorio uploads/ inexistente";
            }
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Ocurrió un error obteniendo la ruta del directorio uploads/ - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $urls : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * obtener la ruta física del directorio uploads
     * @Rest\Post("/uploads", name="uploadsFile")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Obtiene la ruta física y la URL del directorio uploads/"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Ha ocurrido un error obteniendo la ruta física y URL del directorio uploads/"
     * )
     *
     * @SWG\Parameter(name="file", in="body", type="file", description="The photo file", schema={}, required=true)   
     *
     * @SWG\Tag(name="Utilities")
     */
    public function uploadsAction(Request $request): Response
    {

        $serializer = $this->get('jms_serializer');

        $name = $request->request->get("name", null);

        echo $_FILES['file']['name'];

        try {
            $code = 200;
            $error = false;
            $response = "";

            //$uploads = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath().'/uploads/';
            //$dir_uploads = $this->getParameter('dir_uploads');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $this->getParameter('dir_uploads') . basename($_FILES["file"]["name"]))) {
                //$response = "Archivo subido";
                $gestor = opendir($this->getParameter('dir_uploads'));
                while (($archivo = readdir($gestor)) !== false) {

                    $ruta_completa = $this->getParameter('dir_uploads') . "/" . $archivo;

                    // Se muestran todos los archivos y carpetas excepto "." y ".."
                    if ($archivo != "." && $archivo != "..") {

                        $response .= $ruta_completa;
                    }
                }
            } else {
                $code = 404;
                $error = true;
                $message = "Directorio uploads/ inexistente";
            }
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Ocurrió un error obteniendo la ruta del directorio uploads/ - Error: {$ex->getMessage()}";
        }


        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $response : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }
}
