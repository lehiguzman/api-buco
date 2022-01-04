<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Verificar Disponibilidad de profesional
 *
 * @author Strapp International Inc.
 */
class VerificarDisponibilidad
{
    protected $em;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function verificar($profesional)
    {
        
        $verificado = false;
        $ordenes = $this->em->getRepository("App:OrdenServicioProfesional")->findOrdenServicioProfesionalActivo($profesional->getId());    
        
        foreach ($ordenes as $orden) {            
            $d = new \DateTime('NOW');

            $fechaActual = $d->format("Y-m-d");
            $fechaOrden = $orden->getOrdenServicio()->getFechaHora()->format("Y-m-d");
            $diff = $d->diff($orden->getOrdenServicio()->getFechaHora());

            if($fechaActual == $fechaOrden) {
                if($diff->h == 0 && $diff->i <= 40) {                                                   
                    if(in_array(intval($orden->getProfesional()->getEstatus()), [1, 4]) && $orden->getEstatus() == 1 && in_array(intval($orden->getOrdenServicio()->getEstatus()), [2, 4])) {
                        $verificado = true;
                        $orden->getProfesional()->setEstatus(3);
                        $this->em->persist($orden);
                        $this->em->flush();
                    }                    
                }
            }           
        } 
        return $verificado;
    }
}
