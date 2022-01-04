<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="TipoTarifa")
 * @ORM\Entity(repositoryClass="App\Repository\TipoTarifaRepository")
 * @ExclusionPolicy("all")
 */
class TipoTarifa
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
     * Tipo [1:Monto fijo - 2:Monto variable]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $tipo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $valor;

    /**
     * Estatus [1:Activo - 0:Inactivo]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $estatus = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * Fecha de eliminado
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

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

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function setValor(?float $valor): self
    {
        $this->valor = $valor;

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
}
