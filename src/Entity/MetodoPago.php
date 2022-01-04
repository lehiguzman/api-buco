<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="MetodoPago")
 * @ORM\Entity(repositoryClass="App\Repository\MetodoPagoRepository")
 * @ExclusionPolicy("all")
 */
class MetodoPago
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Expose()
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $status = 1;

    /**
     * @ORM\Column(type="boolean")
     * @Expose()
     */
    private $pagoLinea = false;

    /**
     * @ORM\Column(type="boolean")
     * @Expose()
     */
    private $requiereVuelto = false;

    /**
     * @ORM\Column(type="boolean")
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPagoLinea(): ?bool
    {
        return $this->pagoLinea;
    }

    public function setPagoLinea(?bool $pagoLinea): self
    {
        $this->pagoLinea = $pagoLinea;

        return $this;
    }

    public function getRequiereVuelto(): ?bool
    {
        return $this->requiereVuelto;
    }

    public function setRequiereVuelto(?bool $requiereVuelto): self
    {
        $this->requiereVuelto = $requiereVuelto;

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
