<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="TipoDocumento")
 * @ORM\Entity(repositoryClass="App\Repository\TipoDocumentoRepository")
 * @ExclusionPolicy("all")
 */
class TipoDocumento
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
     * TipoVencimiento [1:Fecha específica - 2:Periódicamente - 3:Nunca vence]
     * @ORM\Column(name="tipo_vencimiento", type="integer")
     * @Expose()
     */
    private $tipoVencimiento;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $periodicidad;

    /**
     * @ORM\Column(name="requiere_verificacion", type="boolean", nullable=true)
     * @Expose()
     */
    private $requiereVerificacion = false;

    /**
     * @ORM\Column(name="requiere_copia", type="boolean", nullable=true)
     * @Expose()
     */
    private $requiereCopia = false;

    /**
     * Estatus [1:Activo - 0:Inactivo]
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $estatus;

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

    public function getTipoVencimiento(): ?int
    {
        return $this->tipoVencimiento;
    }

    public function setTipoVencimiento(int $tipoVencimiento): self
    {
        $this->tipoVencimiento = $tipoVencimiento;

        return $this;
    }

    public function getPeriodicidad(): ?int
    {
        return $this->periodicidad;
    }

    public function setPeriodicidad(?int $periodicidad): self
    {
        $this->periodicidad = $periodicidad;

        return $this;
    }

    public function getRequiereVerificacion(): ?bool
    {
        return $this->requiereVerificacion;
    }

    public function setRequiereVerificacion(?bool $requiereVerificacion): self
    {
        $this->requiereVerificacion = $requiereVerificacion;

        return $this;
    }

    public function getRequiereCopia(): ?bool
    {
        return $this->requiereCopia;
    }

    public function setRequiereCopia(?bool $requiereCopia): self
    {
        $this->requiereCopia = $requiereCopia;

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
}
