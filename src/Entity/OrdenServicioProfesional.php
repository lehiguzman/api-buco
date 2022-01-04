<?php

namespace App\Entity;

use App\Repository\OrdenServicioProfesionalRepository;
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="OrdenServicioProfesional")
 * @ORM\Entity(repositoryClass=OrdenServicioProfesionalRepository::class)
 */
class OrdenServicioProfesional
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrdenServicio")
     * @ORM\JoinColumn(name="orden_servicio_id", referencedColumnName="id")
     */
    private $ordenServicio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profesional")
     * @ORM\JoinColumn(name="profesional_id", referencedColumnName="id")
     */
    private $profesional;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $estatus = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Expose()
     */
    private $fechaHoraInicio;

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

    public function getProfesional(): ?Profesional
    {
        return $this->profesional;
    }

    public function setProfesional(?Profesional $profesional): self
    {
        $this->profesional = $profesional;

        return $this;
    }

    public function getEstatus(): ?int
    {
        return $this->estatus;
    }

    public function setEstatus(?int $estatus): self
    {
        $this->estatus = $estatus;

        return $this;
    }

    public function getFechaHoraInicio(): ?\DateTimeInterface
    {
        return $this->fechaHoraInicio;
    }

    public function setFechaHoraInicio(\DateTimeInterface $fechaHoraInicio): self
    {
        $this->fechaHoraInicio = $fechaHoraInicio;

        return $this;
    }
}
