<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="OrdenServicioTarea")
 * @ORM\Entity(repositoryClass="App\Repository\OrdenServicioTareaRepository")
 */
class OrdenServicioTarea
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrdenServicio")
     * @ORM\JoinColumn(name="orden_servicio_id", referencedColumnName="id")
     */
    protected $ordenServicio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tarea")
     * @ORM\JoinColumn(name="tarea_id", referencedColumnName="id")
     */
    protected $tarea;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $cantidad;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $monto;

    /**
     * @ORM\Column(type="integer")
     */
    private $estatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdenServicio(): ?OrdenServicio
    {
        return $this->ordenServicio;
    }

    public function setOrdenServicio(?OrdenServicio $ordenServicio): self
    {
        $this->ordenServicio = $ordenServicio;

        return $this;
    }

    public function getTarea(): ?Tarea
    {
        return $this->tarea;
    }

    public function setTarea(?Tarea $tarea): self
    {
        $this->tarea = $tarea;

        return $this;
    }

    public function getMonto(): ?float
    {
        return $this->monto;
    }

    public function setMonto(?float $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(?int $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getEstatus(): ?int
    {
        return $this->estatus;
    }

    public function setEstatus(int $estatus): self
    {
        $this->estatus = $estatus;

        return $this;
    }
}
