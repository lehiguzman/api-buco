<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Documento")
 * @ORM\Entity(repositoryClass="App\Repository\DocumentoRepository")
 * @ExclusionPolicy("all")
 */
class Documento
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
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $ruta;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profesional")
     * @ORM\JoinColumn(name="profesional_id", referencedColumnName="id")
     * @Expose()
     */
    protected $profesional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoDocumento")
     * @ORM\JoinColumn(name="tipo_documento_id", referencedColumnName="id")
     * @Expose()
     */
    protected $tipoDocumento;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Expose()
     */
    private $copia;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Expose()
     */
    private $fechaVencimiento;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Expose()
     */
    private $vencido = false;

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

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): self
    {
        $this->ruta = $ruta;

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

    public function getTipoDocumento(): ?TipoDocumento
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(?TipoDocumento $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;

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

    public function getCopia(): ?string
    {
        return $this->copia;
    }

    public function setCopia(?string $copia): self
    {
        $this->copia = $copia;

        return $this;
    }

    public function getFechaVencimiento(): ?\DateTimeInterface
    {
        return $this->fechaVencimiento;
    }

    public function setFechaVencimiento(?\DateTimeInterface $fechaVencimiento): self
    {
        $this->fechaVencimiento = $fechaVencimiento;

        return $this;
    }

    public function getVencido(): ?bool
    {
        return $this->vencido;
    }

    public function setVencido(?bool $vencido): self
    {
        $this->vencido = $vencido;

        return $this;
    }
}
