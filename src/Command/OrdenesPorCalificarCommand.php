<?php

namespace App\Command;

use App\Service\FirebaseServicio;
use App\Service\GestionPushTokens;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdenesPorCalificarCommand extends Command
{
    protected static $defaultName = 'Buco:ODSPorCalificar';
    protected $em;
    protected $firebase;
    protected $gestionTokens;

    public function __construct(EntityManagerInterface $entityManager, GestionPushTokens $gestionTokens, FireBaseServicio $firebase)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->firebase = $firebase;
        $this->gestionTokens = $gestionTokens;
    }

    protected function configure()
    {
        $this->setDescription('Notificación al Cliente de Orden de Servicio por Calificada');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ordenes = $this->em->getRepository("App:OrdenServicio")->findBy([
            'estatus' => 7 // ODS Pagada/Finalizada
        ]);

        foreach ($ordenes as $ODS) {
            /** INICIO - ENVIO DE NOTIFICACIONES */
            try {
                $ordenID = $ODS->getId();
                $usuarioTokens = $this->gestionTokens->usuarioPushTokens($ODS->getUser()->getId());
                $notificacion = [
                    "¡Orden de Servicio #$ordenID ya puede ser Calificada!",
                    "Puedes calificar al Profesional en la sección de Valoraciones."
                ];

                if (count($usuarioTokens) > 0) {
                    $data = $this->em->getRepository("App:OrdenServicio")->findODSArray($ODS->getId())[0];
                    $data['tipo'] = "ods";

                    foreach ($usuarioTokens as $value) {
                        $this->firebase->pushNotificacion($value['token'], $data, $notificacion);
                    }
                }
            } catch (Exception $ex) {
                // NO RETORAR NADA 
            }
            /** FIN - ENVIO DE NOTIFICACIONES */
        }

        return 0;
    }
}
