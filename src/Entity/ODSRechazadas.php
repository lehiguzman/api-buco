<?php

namespace App\Entity;

use App\Repository\ODSRechazadasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ODSRechazadas")
 * @ORM\Entity(repositoryClass=ODSRechazadasRepository::class)
 */
class ODSRechazadas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profesional")
     * @ORM\JoinColumn(name="profesional_id", referencedColumnName="id")
     */
    private $profesional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrdenServicio")
     * @ORM\JoinColumn(name="orden_servicio_id", referencedColumnName="id")     
     */
    private $ordenServicio;

    /**
     * Estado [0: Sin Procesar 1: Procesada]
     * @ORM\Column(type="integer")
     */
    private $estado = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaHoraRechazo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPenalizado;


    public function __construct()
    {
        $this->fechaHoraRechazo = new \DateTime();
    }

    public function __toString()
    {
        return (string) $this->getProfesional();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfesional(): ?Profesional
    {
        return $this->profesional;
    }

    public function setProfesional(?Profesional $profesional): self
    {
        $this->profesional = $profesional;

        return $this;
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

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(int $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return string
     */
    public function getEstadoTexto(): ?string
    {
        switch ($this->getEstado()) {
            default:
                $text = 'No Indicado';
                break;
            case 0:
                $text = 'Sin Procesar';
                break;
            case 1:
                $text = 'Procesada';
                break;
        }

        return $text;
    }

    public function getFechaHoraRechazo(): ?\DateTimeInterface
    {
        return $this->fechaHoraRechazo;
    }

    public function setFechaHoraRechazo(\DateTimeInterface $fechaHoraRechazo): self
    {
        $this->fechaHoraRechazo = $fechaHoraRechazo;

        return $this;
    }

    public function getFechaPenalizado(): ?\DateTimeInterface
    {
        return $this->fechaPenalizado;
    }

    public function setFechaPenalizado(?\DateTimeInterface $fechaPenalizado): self
    {
        $this->fechaPenalizado = $fechaPenalizado;

        return $this;
    }
}
