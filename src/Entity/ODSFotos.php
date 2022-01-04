<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ODSFotos")
 * @ORM\Entity(repositoryClass="App\Repository\ODSFotosRepository")
 */
class ODSFotos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrdenServicio")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ordenServicio;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre = "";

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $ruta;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $fechaEliminado = NULL;

    /**
     * @ORM\Column(type="boolean")
     */
    private $eliminado = FALSE;


    public function __construct()
    {
        $this->fechaRegistro = new \DateTime();
    }

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

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

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

    public function setEliminado(bool $eliminado): self
    {
        $this->eliminado = $eliminado;

        return $this;
    }
}