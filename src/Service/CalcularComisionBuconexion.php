<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Calcular Comisión de Buconexion de ODS
 *
 * @author Strapp International Inc.
 */
class CalcularComisionBuconexion
{
    protected $em;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function costoBuconexion($ODS)
    {
        $montoTotal = $ODS->getMonto();
        $listaCostos = $this->em->getRepository("App:ConfigCostoComision")->findAll();
        $montoComision = 0.0;

        foreach ($listaCostos as $key => $costo) {
            if ($montoTotal >= $costo->getRangoA() && $montoTotal < ($costo->getRangoB() + 1)) {
                if ($costo->getPorcentaje()) {
                    $montoComision = $montoTotal * ($costo->getCostoBuconexion() / 100);
                } else {
                    $montoComision = $costo->getCostoBuconexion();
                }
            }

            if ($montoTotal >= $costo->getRangoA() && $costo->getRangoB() == -1 && $costo->getPorcentaje()) {
                $montoComision = $montoTotal * ($costo->getCostoBuconexion() / 100);
            }
        }

        $ODS->setComisionBuconexion($this->convertToFloat($montoComision));

        return $ODS;
    }

    private function convertToFloat($number)
    {
        return floatval(sprintf("%.2f", number_format($number, 2, '.', '')));
    }
}
