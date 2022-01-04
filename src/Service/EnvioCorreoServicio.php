<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

/**
 * https://symfony.com/doc/current/email.html
 * https://github.com/symfony/swiftmailer-bundle
 * https://swiftmailer.symfony.com/docs/messages.html
 *
 * Plantillas de Correo:
 * - https://mjml.io/
 * - https://mjml.io/try-it-live
 * - https://mjml.io/documentation/
 */
class EnvioCorreoServicio
{
    private $emailFrom;
    private $emailName;
    private $appNombre;
    private $twig;
    protected $em;
    protected $mailer;
    protected $templating;

    function __construct(EntityManagerInterface $entityManager, $mailer, $emailFrom, $emailName, $appNombre, Environment $twig)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->emailFrom = $emailFrom;
        $this->emailName = $emailName;
        $this->appNombre = $appNombre;
        $this->twig = $twig;

        $this->templating = $this->twig->render('email/base.strappinc.html.twig');
    }

    private function setTemplate($templatePath, $data = NULL)
    {
        try {
            $this->templating = $this->twig->render($templatePath, $data);
        } catch (\Twig\Error\LoaderError $le) {
            return [
                'error' => true,
                'mensaje' => "La ruta de la plantilla no es válida.",
                'descripcion' => $le->getMessage(),
            ];
        } catch (\Throwable $th) {
            return [
                'error' => true,
                "excepcion" => $th
            ];
            throw $th;
        }

        return true;
    }

    private function sendMail($subject, $emailTo, $emailCC = NULL, $attachmentPaths = [])
    {
        try {
            $rutaValida = true;
            $rutaArchivos = [];
            if ($attachmentPaths) {
                if (is_array($attachmentPaths)) {
                    foreach ($attachmentPaths as $key => $path) {
                        if (!file_exists($path)) {
                            $rutaValida = false;
                            $rutaArchivos[] = $path;
                        }
                    }
                } elseif (is_string($attachmentPaths)) {
                    if (!file_exists($attachmentPaths)) {
                        $rutaValida = false;
                        $rutaArchivos[] = $attachmentPaths;
                    }
                }
            }
            if ($rutaValida === false) {
                return [
                    'error' => true,
                    'mensaje' => "Ruta del archivo no válida.",
                    'rutas' => $rutaArchivos,
                ];
            }

            if (!$this->mailer->getTransport()->isStarted()) {
                $this->mailer->getTransport()->start();
            }

            /* @var $message \Swift_Message */
            $message = $this->mailer->createMessage();
            $message->setContentType("text/html");
            $message->setSubject("$this->appNombre | $subject");
            $message->setFrom(array($this->emailFrom => $this->emailName));
            $message->setTo($emailTo);

            if ($emailCC) {
                $message->setCc($emailCC);
            }

            if ($rutaValida && $attachmentPaths) {
                if (is_array($attachmentPaths)) {
                    foreach ($attachmentPaths as $key => $value) {
                        $message->attach(\Swift_Attachment::fromPath($value));
                    }
                } elseif (is_string($attachmentPaths)) {
                    $message->attach(\Swift_Attachment::fromPath($attachmentPaths));
                }
            }

            $message->setBody($this->templating, 'text/html');

            $this->mailer->send($message);
            $this->mailer->getTransport()->stop();
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }

        return true;
    }

    /**
     * @method enviar
     * @param array [asunto, correos, concopia, adjuntos (array), plantilla, datos]
     * @return boolean true o false (error)
     * 
     * asunto: titulo del correo
     * correos y concopia:
     *   formatos permitidos
     *     correo@mail.com
     *     array('correo@mail.com' => 'Fulano de Tail')
     *     array('correo@mail.com', correo2@mail.com)
     * ajuntos: rutas absolutas del archivo
     * plantilla: ruta del template
     * datos: objeto/arreglo de datos
     */
    public function enviar($parametros = [])
    {
        if (isset($parametros['asunto']) && isset($parametros['correos'])) {
            $asunto = $parametros['asunto'];
            $correos = $parametros['correos'];
            $concopia = isset($parametros['concopia']) ? $parametros['concopia'] : null;
            $adjuntos = isset($parametros['adjuntos']) ? $parametros['adjuntos'] : null;

            if (isset($parametros['plantilla'])) {
                $datos = isset($parametros['datos']) ? $parametros['datos'] : [];
                $datos['asunto'] = $parametros['asunto'];
                $resp = $this->setTemplate($parametros['plantilla'], $datos);
                if (isset($resp['error']) && $resp['error']) {
                    return $resp;
                }
            }

            return $this->sendMail($asunto, $correos, $concopia, $adjuntos);
        } else {
            return false;
        }
    }
}
