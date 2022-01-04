<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProfesionalesPenalizadosCommand extends Command
{
    protected static $defaultName = 'Buco:ProfesionalesPenalizados';
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Inactiva o Activa al Profesional Penalizado');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // obtener la configuración de dias a penalizar
        $diasPenalizado = $this->em->getRepository("App:Configuracion")->findOneBy([
            'nombre' => 'profesional_penalizado_dias'
        ]);
        if ($diasPenalizado) {
            $dias = $diasPenalizado->getValor();

            $io->title("Buscar Profesionales a Penalizar");
            // bucar Profesionales que pueden ser Penalizados
            $profesionales = $this->em->getRepository("App:Profesional")->findProfesionalesODSRechazadas();
            foreach ($profesionales as $profesional) {
                $textoSalida = [];
                // var_dump($profesional);
                $ordenes = $this->em->getRepository("App:ODSRechazadas")->findByProfesional($profesional->getId());
                $textoSalida[] = $profesional->__toString() . " | ODS Rechazadas: " . count($ordenes);
                $ahora = new \DateTime();

                foreach ($ordenes as $ODSRechazada) {
                    $ODSRechazada->setEstado(1);
                    $ODSRechazada->setFechaPenalizado($ahora);
                    $this->em->persist($ODSRechazada);
                }

                $profesional->setFechaPenalizadoInicio($ahora);
                $fechaFin =  new \DateTime(date('Y-m-d', strtotime($ahora->format('y-m-d') . " + $dias days")));
                $textoSalida[] = "Fecha Inicio: " . $ahora->format('y-m-d H:i:00') . " | Fecha Fin: " . $fechaFin->format('Y-m-d H:i:00');
                $profesional->setFechaPenalizadoFin($fechaFin);
                $profesional->setEstatus(2);  // set Penalizado
                $profesional->setOrdenesRechazadas(0);
                $this->em->persist($profesional);

                $textoSalida[] = "";
                $io->text($textoSalida);
            }
            if (count($profesionales) === 0) {
                $io->warning('No se encontraron Profesionales');
            }
        } else {
            $io->warning('No se encontro la configuración para Profesionales Penalizados');
        }

        $io->title("Buscar Profesionales Penalizados");
        // buscar Profesionales Penalizados que pueden Activarse
        $profesionales = $this->em->getRepository("App:Profesional")->findBy([
            'estatus' => 2,
            'eliminado' => 0
        ]);
        foreach ($profesionales as $profesional) {
            $textoSalida = [];
            $ahora = new \DateTime();

            $fechaFin = $profesional->getFechaPenalizadoFin();
            $fechaActual = strtotime(date("d-m-Y H:i:00", time()));
            $fechaFinPenalizacion = strtotime($fechaFin->format("d-m-Y H:i:00"));

            $textoSalida[] = $profesional->__toString();
            $textoSalida[] = "fecha actual: " . $ahora->format('y-m-d') . " fecha fin: " . $fechaFin->format("d-m-Y");

            // si la fecha Actual es mayor a la fecha fin de Penalización, 
            // el Profesional Puede activarse
            if ($fechaActual > $fechaFinPenalizacion) {
                $textoSalida[] = "Activando Profesional. Su estado seŕa Desconectado";
                $profesional->setEstatus(4);  // Desconectado
                $profesional->setFechaPenalizadoInicio(NULL);
                $profesional->setFechaPenalizadoFin(NULL);
                $this->em->persist($profesional);
            } else {
                $textoSalida[] = "Aun no se cumple la fecha fin de la Penalización";
            }

            $textoSalida[] = "";
            $io->text($textoSalida);
        }
        if (count($profesionales) === 0) {
            $io->warning('No se encontraron Profesionales');
        }

        $this->em->flush();
        return 0;
    }
}
