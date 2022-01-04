<?php

namespace App\Entity;

use App\Repository\ServicioCamposEspecificosValoresRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ServicioCamposEspecificosValores")
 * @ORM\Entity(repositoryClass=ServicioCamposEspecificosValoresRepository::class)
 */
class ServicioCamposEspecificosValores
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Servicio", cascade={"persist"})
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id", nullable=FALSE)
     */
    private $servicio;

    /**
     * @ORM\ManyToOne(targetEntity="FormularioDinamicoServicio", cascade={"persist"})
     * @ORM\JoinColumn(name="formulario_dinamico_servicio_id", referencedColumnName="id", nullable=FALSE)
     */
    private $FormularioDinamicoServicio;

    /**
     * @ORM\ManyToOne(targetEntity="Profesional", cascade={"persist"})
     * @ORM\JoinColumn(name="profesional_id", referencedColumnName="id", nullable=FALSE)
     */
    private $profesional;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=5000)
     */
    private $valor;

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

    public function __toString()
    {
        return (string) $this->getNombre();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getFormularioDinamicoServicio(): ?FormularioDinamicoServicio
    {
        return $this->FormularioDinamicoServicio;
    }

    public function setFormularioDinamicoServicio(?FormularioDinamicoServicio $FormularioDinamicoServicio): self
    {
        $this->FormularioDinamicoServicio = $FormularioDinamicoServicio;

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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

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
