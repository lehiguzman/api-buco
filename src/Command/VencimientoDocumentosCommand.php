<?php

namespace App\Command;

use App\Entity\Notificacion;
use App\Service\SendEmails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VencimientoDocumentosCommand extends Command
{
    protected static $defaultName = 'Buco:VencimientoDocumentos';
    protected $em;
    private $sendEmails;

    public function __construct(EntityManagerInterface $entityManager, SendEmails $sendEmails)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->sendEmails = $sendEmails;
    }

    protected function configure()
    {
        $this->setDescription('Aviso de vencimiento de los documentos de los profesionales.')
            ->setHelp('Consulta aquellos documentos de los profesionales que están por vencerse y los que están vencidos.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Documentos que están por vencer (15 días antes) y los vencidos
        $fifteen = date('Y-m-d', strtotime(date("Y-m-d", strtotime("15 day"))));
        $hoy = date('Y-m-d');

        $dql = "SELECT d.nombre as doc, 
                td.nombre as tipo, 
                u.id as userId, 
                u.name as usuario, 
                u.email as email, 
                d.fechaVencimiento as vencimiento 
                FROM App:Documento d 
                INNER JOIN App:TipoDocumento td WITH td.id = d.tipoDocumento  
                INNER JOIN App:Profesional p WITH p.id = d.profesional 
                INNER JOIN App:User u WITH u.id = p.user 
                WHERE (p.eliminado = 0 OR p.eliminado IS NULL) 
                AND p.estatus = 1 
                AND (u.eliminado = 0 OR u.eliminado IS NULL) 
                AND u.isActive = 1 
                AND d.vencido = 0 
                AND (d.fechaVencimiento = :por_vencer OR d.fechaVencimiento < :hoy)";
        $query = $this->em->createQuery($dql);
        $query->setParameters(array(
            'por_vencer' => $fifteen,
            'hoy'        => $hoy
        ));
        $documentos = $query->getResult();

        $output->writeln([
            '',
            'Documentos a vencerse el ' . $fifteen . ' y los vencidos',
            'Cantidad de registros encontrados: ' . count($documentos),
            '=======================================',
        ]);

        foreach ($documentos as $d) {
            $vencido = ($d["vencimiento"]->format('Y-m-d') < $hoy) ? 1 : 0;
            $estado = $vencido ? 'Vencido' : 'Por vencer';
            $output->writeln([
                'Documento: ' . $d["doc"],
                'Tipo: ' . $d["tipo"],
                'UserId: ' . $d["userId"],
                'Profesional: ' . $d["usuario"],
                'Email: ' . $d["email"],
                'Vencimiento: ' . $d["vencimiento"]->format('d/m/Y'),
                'Estado: ' . $estado,
            ]);

            // Crear la notificación
            $user = $this->em->getRepository("App:User")->find($d["userId"]);
            $asunto = $vencido ? 'Documento vencido' : 'Documento está por vencer';
            $descripcion = $vencido ? 'El documento ' . $d["tipo"] . ' venció el ' . $d["vencimiento"]->format('d/m/Y') : 'El documento ' . $d["tipo"] . ' está por vencerse el ' . $d["vencimiento"]->format('d/m/Y');
            $notificacion = new Notificacion();
            $notificacion->setUser($user);
            $notificacion->setAsunto($asunto);
            $notificacion->setDescripcion($descripcion);
            $this->em->persist($notificacion);
            $this->em->flush();

            // Enviar el correo
            $setTo = $d["email"];
            $content = '<p>Estimado ' . $d["usuario"] . ':</p>';
            $content .= '<p>' . $descripcion . '.</p>';
            if ($vencido) {
                $content .= '<p>Le invitamos que ingrese desde la aplicación a la sección de <b>Mis Documentos</b>, donde podrá adjuntar este documento y renovar su fecha de vencimiento. Además le permitirá atender nuevas órdenes de servicio.</p>';
            } else {
                $content .= '<p>Le sugerimos que tramite la renovación de dicho documento, de manera que llegada la fecha de vencimiento pueda adjuntar el documento renovado a través de la sección de <b>Mis Documentos</b>.</p>';
            }
            $data = ['content' => $content];
            $this->sendEmails->setTemplate('email/avisoVencimiento.html.twig', $data);
            $this->sendEmails->send($asunto, $setTo);

            $output->writeln([
                '---------------------------------------',
                '',
            ]);
        }

        return 0;
    }
}
