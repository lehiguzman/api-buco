<?php

namespace App\Entity;

use App\Repository\FormularioDinamicoServicioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FormularioDinamicoServicio")
 * @ORM\Entity(repositoryClass=FormularioDinamicoServicioRepository::class)
 */
class FormularioDinamicoServicio
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
     * @ORM\ManyToOne(targetEntity="FormularioDinamico", cascade={"persist"})
     * @ORM\JoinColumn(name="formulario_dinamico_id", referencedColumnName="id", nullable=FALSE)
     */
    private $formularioDinamico;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=60, unique=TRUE)
     */
    private $clave;

    /**
     * @ORM\Column(type="string", length=500, nullable=TRUE)
     */
    private $opciones = NULL;

    /**
     * @ORM\Column(type="integer")
     */
    private $longitudMinima = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $longitudMaxima = 5000;

    /**
     * @ORM\Column(type="boolean")
     */
    private $requerido = FALSE;

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

    public function getFormularioDinamico(): ?FormularioDinamico
    {
        return $this->formularioDinamico;
    }

    public function setFormularioDinamico(?FormularioDinamico $formularioDinamico): self
    {
        $this->formularioDinamico = $formularioDinamico;

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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoTexto(): ?string
    {
        switch ($this->getTipo()) {
            case 'text':
                $text = 'Texto';
                break;
            case 'number':
                $text = 'Númerico';
                break;
            case 'decimal':
                $text = 'Númerico con Decimales';
                break;
            case 'money':
                $text = 'Dinero';
                break;
            case 'date':
                $text = 'Fecha';
                break;
            case 'time':
                $text = 'Hora';
                break;
            case 'datetime':
                $text = 'Fecha y Hora';
                break;
            case 'boolean':
                $text = 'Si o No';
                break;
            case 'selectsimple':
                $text = 'Selección Simple';
                break;
            case 'selectmultiple':
                $text = 'Selección Múltiple';
                break;
        }

        return $text;
    }

    public function getClave(): ?string
    {
        return $this->clave;
    }

    public function setClave(string $clave): self
    {
        $this->clave = $clave;

        return $this;
    }

    public function getOpciones(): ?string
    {
        return $this->opciones;
    }

    public function setOpciones(?string $opciones): self
    {
        $this->opciones = $opciones;

        return $this;
    }

    public function getLongitudMinima(): ?int
    {
        return $this->longitudMinima;
    }

    public function setLongitudMinima(int $longitudMinima): self
    {
        $this->longitudMinima = $longitudMinima;

        return $this;
    }

    public function getLongitudMaxima(): ?int
    {
        return $this->longitudMaxima;
    }

    public function setLongitudMaxima(int $longitudMaxima): self
    {
        $this->longitudMaxima = $longitudMaxima;

        return $this;
    }

    public function getRequerido(): ?bool
    {
        return $this->requerido;
    }

    public function setRequerido(bool $requerido): self
    {
        $this->requerido = $requerido;

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
