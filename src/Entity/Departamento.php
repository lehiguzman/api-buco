<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Departamento")
 * @ORM\Entity(repositoryClass="App\Repository\DepartamentoRepository")
 * @ExclusionPolicy("all")
 */
class Departamento
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
     * Estatus [1:Activo - 0:Inactivo]
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $estatus;

    /**
     * SistemaTipo [1:bucoservicio - 2:bucotalento]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $sistemaTipo = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * Fecha de eliminado
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * Icono
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Expose()
     */
    private $icono;

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

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }
}
