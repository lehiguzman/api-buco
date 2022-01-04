<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Tarea")
 * @ORM\Entity(repositoryClass="App\Repository\TareaRepository")
 * @ExclusionPolicy("all")
 */
class Tarea
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Expose()
     */
    private $descripcion;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $tarifaMinima;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $tarifaMaxima;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $estatus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servicio")
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id")
     * @Expose()
     */
    protected $servicio;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getTarifaMinima(): ?float
    {
        return $this->tarifaMinima;
    }

    public function setTarifaMinima(?float $tarifaMinima): self
    {
        $this->tarifaMinima = $tarifaMinima;

        return $this;
    }

    public function getTarifaMaxima(): ?float
    {
        return $this->tarifaMaxima;
    }

    public function setTarifaMaxima(?float $tarifaMaxima): self
    {
        $this->tarifaMaxima = $tarifaMaxima;

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

    public function getEliminado(): ?bool
    {
        return $this->eliminado;
    }

    public function setEliminado(?bool $eliminado): self
    {
        $this->eliminado = $eliminado;

        return $this;
    }

    public function getFechaEliminado(): ?\DateTimeInterface
    {
        return $this->fechaEliminado;
    }

    public function setFechaEliminado(?\DateTimeInterface $fechaEliminado): self
    {
        $this->fechaEliminado = $fechaEliminado;

        return $this;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }
}
