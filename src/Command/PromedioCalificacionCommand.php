<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromedioCalificacionCommand extends Command
{
    protected static $defaultName = 'Buco:PromedioCalificacion';
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Promedio de calificaciones por profesional.')
            ->setHelp('Cálculo del promedio para los items puntualidad, servicio, presencia, conocimiento y recomendado por cada profesional.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $records = $this->em->getRepository("App:Calificacion")->findCalificaciones();

        foreach ($records as $r) {

            $output->writeln([
                'ID: ' . $r["id"],
                'Profesional: ' . $r["nombre"] . ' ' . $r["apellido"],
                'Puntualidad: ' . $r["promedio_puntualidad"],
                'Servicio: ' . $r["promedio_servicio"],
                'Presencia: ' . $r["promedio_presencia"],
                'Conocimiento: ' . $r["promedio_conocimiento"],
                'Recomendado: ' . $r["promedio_recomendado"],
                'GENERAL: ' . $r["promedio_general"],
                'Valoraciones: ' . $r["conteo"],
            ]);

            $profesional = $this->em->getRepository("App:Profesional")->find($r["id"]);
            $profesional->setPromedioPuntualidad($r["promedio_puntualidad"]);
            $profesional->setPromedioServicio($r["promedio_servicio"]);
            $profesional->setPromedioPresencia($r["promedio_presencia"]);
            $profesional->setPromedioConocimiento($r["promedio_conocimiento"]);
            $profesional->setPromedioRecomendado($r["promedio_recomendado"]);
            $this->em->persist($profesional);
            $this->em->flush();

            $output->writeln([
                'Información actualizada del profesional',
                '=======================================',
                '',
            ]);
        }

        return 0;
    }
}
