<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="ArchivoPortafolio")
 * @ORM\Entity(repositoryClass="App\Repository\ArchivoPortafolioRepository")
 * @ExclusionPolicy("all")
 */
class ArchivoPortafolio
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
     * @Expose()
     */
    protected $profesional;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ruta;

    /**
     * @ORM\Column(type="integer")
     */
    private $tipoArchivo;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaCreacion;

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

    public function getProfesional(): ?Profesional
    {
        return $this->profesional;
    }

    public function setProfesional(?Profesional $profesional): self
    {
        $this->profesional = $profesional;

        return $this;
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

    public function getTipoArchivo(): ?int
    {
        return $this->tipoArchivo;
    }

    public function setTipoArchivo(int $tipoArchivo): self
    {
        $this->tipoArchivo = $tipoArchivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoArchivoTexto(): ?string
    {
        switch ($this->getTipoArchivo()) {
            default:
            case 1:
                $text = 'Documento';
                break;
            case 2:
                $text = 'Imagen';
                break;
            case 3:
                $text = 'Video';
                break;
        }

        return $text;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

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
