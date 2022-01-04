<?php

namespace App\Repository;

use App\Entity\OrdenServicioTarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrdenServicioTarea|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdenServicioTarea|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdenServicioTarea[]    findAll()
 * @method OrdenServicioTarea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdenServicioTareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdenServicioTarea::class);
    }

    public function findbyServiceOrder($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT ost FROM App:OrdenServicioTarea ost 
                                    JOIN ost.tarea t 
                                    JOIN ost.ordenServicio os 
                                    WHERE ost.ordenServicio = :id 
                                    AND (os.eliminado = :not_eliminated OR os.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY ost.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    public function findbyTask($id)
    {

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT ost FROM App:OrdenServicioTarea ost 
                                    JOIN ost.tarea t 
                                    JOIN ost.ordenServicio os 
                                    WHERE ost.tarea = :id 
                                    AND (os.eliminado = :not_eliminated OR os.eliminado IS NULL) 
                                    AND (t.eliminado = :not_eliminated OR t.eliminado IS NULL) 
                                    ORDER BY ost.id ASC")
            ->setParameters(array(
                'id' => intval($id),
                'not_eliminated' => 0
            ));
        $results = $query->getResult();

        //---- Respuesta ----
        return $results;
    }

    /**
     * Calcula el monto de la OS a través de las tareas aprobadas y las comisiones
     * @param integer $orden_servicio_id
     *
     * @return array
     */
    public function calcularMontoOS($orden_servicio_id)
    {

        $em = $this->getEntityManager();

        $monto = 0;
        $comision = 0;
        $orden_servicio = 0;

        $osts = $this->createQueryBuilder('ost')
            ->where('ost.ordenServicio = :orden_servicio_id')
            ->andWhere('ost.estatus = :aprobada')
            ->setParameters(array(
                'orden_servicio_id' => $orden_servicio_id,
                'aprobada' => 1
            ))
            ->getQuery()->getResult();

            foreach ($osts as $ost) {
                $monto += ($ost->getMonto()*$ost->getCantidad());
                $orden_servicio = $ost->getOrdenServicio();
            }

            $query = $em->createQuery("SELECT osp FROM App:OrdenServicioProfesional osp
                                        JOIN osp.profesional p                                                        
                                        WHERE osp.ordenServicio = :id    
                                        AND osp.estatus = 2
                                        AND (p.eliminado = :not_eliminated OR p.eliminado IS NULL) 
                                        ORDER BY osp.id ASC")
                    ->setParameters(
                        array('id' => intval($orden_servicio->getId()),
                        'not_eliminated' => 0));

            $osps = $query->getResult();

            foreach($osps as $osp) {
                // Para las comisiones se verifica el tipo de comisión que tiene asociado el profesional
                //if ($orden_servicio && $orden_servicio->getProfesional()->getComision()) {
                //if ($osp->getProfesional()) { 
                    
                    switch ($osp->getProfesional()->getComision()->getTipo()) {

                        case 1: // Fijo
                            $comision += is_null($osp->getProfesional()->getComision()->getMonto()) ? 0 : $osp->getProfesional()->getComision()->getMonto();
                            break;

                        case 2: // Variable
                            if (is_null($osp->getProfesional()->getComision()->getPorcentaje())) {
                                $comision += 0;
                            } else {
                                $comision += ($osp->getProfesional()->getComision()->getPorcentaje() / 100) * $monto;
                            }
                            break;

                        case 3: // Combinado
                            $comision += is_null($osp->getProfesional()->getComision()->getMonto()) ? 0 : $osp->getProfesional()->getComision()->getMonto();
                            if (!is_null($osp->getProfesional()->getComision()->getPorcentaje())) {
                                $comision += $comision + (($osp->getProfesional()->getComision()->getPorcentaje() / 100) * $monto);
                            }
                            break;
                    }                    
                //}
            }        
        return array(
            'monto'    => $monto,
            'comision' => $comision
        );
    }

    /**
     * @param integer $id
     *
     * @return array
     */
    public function findTareaAprobada($id)
    {
        return $this->createQueryBuilder('ost')
            ->where('ost.estatus = :aprobada')
            ->andWhere('ost.id = :id')
            ->setParameters(array(
                'aprobada' => 1,
                'id' => intval($id)
            ))
            ->getQuery()->getOneOrNullResult();
    }
}
