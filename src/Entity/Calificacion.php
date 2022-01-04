<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Calificacion")
 * @ORM\Entity(repositoryClass="App\Repository\CalificacionRepository")
 */
class Calificacion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Puntualidad
     * @ORM\Column(type="integer")
     */
    private $puntualidad;

    /**
     * Servicio
     * @ORM\Column(type="integer")
     */
    private $servicio;

    /**
     * Presencia / Amabilidad
     * @ORM\Column(type="integer")
     */
    private $presencia;

    /**
     * Dominio / Conocimiento
     * @ORM\Column(type="integer")
     */
    private $conocimiento;

    /**
     * Recomendando [si - no]
     * @ORM\Column(type="integer")
     */
    private $recomendado;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrdenServicio")
     * @ORM\JoinColumn(name="orden_servicio_id", referencedColumnName="id")
     */
    protected $ordenServicio;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $comentarios;


    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPuntualidad(): ?int
    {
        return $this->puntualidad;
    }

    public function setPuntualidad(int $puntualidad): self
    {
        $this->puntualidad = $puntualidad;

        return $this;
    }

    public function getServicio(): ?int
    {
        return $this->servicio;
    }

    public function setServicio(int $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getPresencia(): ?int
    {
        return $this->presencia;
    }

    public function setPresencia(int $presencia): self
    {
        $this->presencia = $presencia;

        return $this;
    }

    public function getConocimiento(): ?int
    {
        return $this->conocimiento;
    }

    public function setConocimiento(int $conocimiento): self
    {
        $this->conocimiento = $conocimiento;

        return $this;
    }

    public function getRecomendado(): ?int
    {
        return $this->recomendado;
    }

    public function setRecomendado(int $recomendado): self
    {
        $this->recomendado = $recomendado;

        return $this;
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

    public function getOrdenServicio(): ?OrdenServicio
    {
        return $this->ordenServicio;
    }

    public function setOrdenServicio(?OrdenServicio $ordenServicio): self
    {
        $this->ordenServicio = $ordenServicio;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }

    public function setComentarios(?string $comentarios): self
    {
        $this->comentarios = $comentarios;

        return $this;
    }
}
