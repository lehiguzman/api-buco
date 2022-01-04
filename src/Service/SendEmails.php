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
 * 
 * @author Strapp International Inc.
 */
class SendEmails
{
    private $emailFrom;
    private $emailName;
    private $twig;
    protected $container;
    protected $em;
    protected $mailer;
    protected $templating;

    function __construct(EntityManagerInterface $entityManager, $mailer, $container, $emailFrom, $emailName, Environment $twig)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->container = $container;
        $this->emailFrom = $emailFrom;
        $this->emailName = $emailName;
        $this->twig = $twig;

        $this->templating = $this->twig->render('email/base.strappinc.html.twig');
    }

    public function enviarCredenciales($datos)
    {
        $usuario = $datos['entity'];
        $subject = "Credenciales de Acceso | " . $this->emailName;
        $emailTo = [$usuario->getEmail() => $usuario->getName()];
        $this->setTemplate('email/app.bienvenido.html.twig', ['user' => $usuario, 'password' => $datos['password']]);
        $this->send($subject, $emailTo);
    }

    public function setTemplate($templatePath, $data = NULL)
    {
        $this->templating = $this->twig->render($templatePath, $data);
    }

    public function send($subject, $emailTo, $emailCC = NULL)
    {
        if (!$this->mailer->getTransport()->isStarted()) {
            $this->mailer->getTransport()->start();
        }

        /* @var $message \Swift_Message */
        $message = $this->mailer->createMessage();
        $message->setContentType("text/html");
        $message->setSubject($subject);
        $message->setFrom(array($this->emailFrom => $this->emailName));
        $message->setTo($emailTo);
        if ($emailCC) {
            $message->setCc($emailCC);
        }
        $message->setBody($this->templating, 'text/html');

        $this->mailer->send($message);
        $this->mailer->getTransport()->stop();

        return true;
    }
}
