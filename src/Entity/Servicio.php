<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Servicio")
 * @ORM\Entity(repositoryClass="App\Repository\ServicioRepository")
 * @ExclusionPolicy("all")
 */
class Servicio
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Expose()
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $estatus = 1;

    /**
     * SistemaTipo [1:bucoservicio - 2:bucotalento]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $sistemaTipo = 1;

    /**
     * comisionTipo [1:fijo - 2:variable]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $comisionTipo = 1;

    /**
     * @ORM\Column(type="float")
     * @Expose()
     */
    private $montoComision = 0.0;

    /**
     * @ORM\Column(type="float")
     * @Expose()
     */
    private $porcentajePenalizacion = 0.0;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Expose()
     */
    private $icono;

    /**
     * @ORM\Column(type="boolean")
     */
    private $camposEspeciales = FALSE;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

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

    public function getSistemaTipo(): ?int
    {
        return $this->sistemaTipo;
    }

    public function setSistemaTipo(int $sistemaTipo): self
    {
        $this->sistemaTipo = $sistemaTipo;

        return $this;
    }

    /**
     * Retorna el Tipo de Sistema en texto
     * 
     * @return string
     */
    public function getSistemaTipoTexto(): ?string
    {
        switch ($this->getSistemaTipo()) {
            default:
                $text = 'Servicio No Especificado';
                break;
            case 1:
                $text = 'Buco Servicio';
                break;
            case 2:
                $text = 'Buco Talento';
                break;
        }

        return $text;
    }

    public function getComisionTipo(): ?int
    {
        return $this->comisionTipo;
    }

    public function setComisionTipo(int $comisionTipo): self
    {
        $this->comisionTipo = $comisionTipo;

        return $this;
    }

    public function getMontoComision(): ?float
    {
        return $this->montoComision;
    }

    public function setMontoComision(?float $montoComision): self
    {
        $this->montoComision = $montoComision;

        return $this;
    }

    public function getPorcentajePenalizacion(): ?float
    {
        return $this->porcentajePenalizacion;
    }

    public function setPorcentajePenalizacion(?float $porcentajePenalizacion): self
    {
        $this->porcentajePenalizacion = $porcentajePenalizacion;

        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

    public function getCamposEspeciales(): ?bool
    {
        return $this->camposEspeciales;
    }

    public function setCamposEspeciales(?bool $camposEspeciales): self
    {
        $this->camposEspeciales = $camposEspeciales;

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
