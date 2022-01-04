<?php

namespace App\Command;

use App\Service\VerificarDisponibilidad;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisponibilidadProfesionalesCommand extends Command
{
    protected static $defaultName = 'Buco:DisponibilidadProfesionales';
    protected $em;
    protected $firebase;
    protected $gestionTokens;

    public function __construct(EntityManagerInterface $entityManager, VerificarDisponibilidad $verificarDisponibilidad)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->verificarDisponibilidad = $verificarDisponibilidad;
    }

    protected function configure()
    {
        $this->setDescription('Actualizar profesionales disponibles');
    }

    //protected function execute()
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /*$profesionales = $this->em->getRepository("App:Profesional")->findByEstatus(array(1));       

        foreach($profesionales as $profesional) {
            $verificado = $this->verificarDisponibilidad->verificar($profesional);                     
            
            if( $verificado ) {
                $output->writeln([
                    'Profesional : '. $profesional->getNombreCompleto(),
                    'Actualizado : Si'
                ]); 
            } else {
                $output->writeln([
                    'Profesional : '. $profesional->getNombreCompleto(),
                    'Actualizado : No'
                ]); 
            }
                       
        }*/      
        
        $strA = "Kangaroo";
        $strB = "doors";

        function longestMatch($strA, $strB) {
            $len_1 = strlen($strA);
            $longestMatch = '';
            for($i = 0; $i < $len_1; $i++){
                for($j = $len_1 - $i; $j > 0; $j--){
                    $substr = substr($strA, $i, $j);
                    if (strpos($strB, $substr) !== false && strlen($substr) > strlen($longestMatch)){
                        $longestMatch = $substr;
                        break;
                    }
                }
            }
            return $longestMatch;
        }
        
        return 0;
    }
}
