<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Comision")
 * @ORM\Entity(repositoryClass="App\Repository\ComisionRepository")
 * @ExclusionPolicy("all")
 */
class Comision
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
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $tipo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $monto;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $porcentaje;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

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

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): self
    {
        $this->tipo = $tipo;

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

    public function getPorcentaje(): ?float
    {
        return $this->porcentaje;
    }

    public function setPorcentaje(?float $porcentaje): self
    {
        $this->porcentaje = $porcentaje;

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

    public function getEliminado(): ?bool
    {
        return $this->eliminado;
    }

    public function setEliminado(?bool $eliminado): self
    {
        $this->eliminado = $eliminado;

        return $this;
    }
}
