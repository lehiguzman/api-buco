<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Direccion")
 * @ORM\Entity(repositoryClass="App\Repository\DireccionRepository")
 * @ExclusionPolicy("all")
 */
class Direccion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Expose()
     */
    private $user;

    /**
     * SistemaTipo [1:bucoservicio - 2:bucotalento]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $sistemaTipo = 1;

    /**
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Expose()
     */
    private $direccion;

    /**
     * @ORM\Column(type="float")
     * @Expose()
     */
    private $latitud;

    /**
     * @ORM\Column(type="float")
     * @Expose()
     */
    private $longitud;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Expose()
     */
    private $residencia;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Expose()
     */
    private $pisoNumero;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Expose()
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Expose()
     */
    private $telefonoMovil;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Expose()
     */
    private $instruccion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $predeterminada = false;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Retorna el Tipo de Sistema en texto
     * 
     * @return string
     */
    public function getTipoTexto(): ?string
    {
        switch ($this->getTipo()) {
            default:
                $text = 'No Especificado';
                break;
            case 1:
                $text = 'Casa';
                break;
            case 2:
                $text = 'Trabajo';
                break;
            case 3:
                $text = 'Otro';
                break;
        }

        return $text;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getLatitud(): ?float
    {
        return $this->latitud;
    }

    public function setLatitud(float $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(float $longitud): self
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getResidencia(): ?string
    {
        return $this->residencia;
    }

    public function setResidencia(?string $residencia): self
    {
        $this->residencia = $residencia;

        return $this;
    }

    public function getPisoNumero(): ?string
    {
        return $this->pisoNumero;
    }

    public function setPisoNumero(?string $pisoNumero): self
    {
        $this->pisoNumero = $pisoNumero;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTelefonoMovil(): ?string
    {
        return $this->telefonoMovil;
    }

    public function setTelefonoMovil(?string $telefonoMovil): self
    {
        $this->telefonoMovil = $telefonoMovil;

        return $this;
    }

    public function getInstruccion(): ?string
    {
        return $this->instruccion;
    }

    public function setInstruccion(?string $instruccion): self
    {
        $this->instruccion = $instruccion;

        return $this;
    }

    public function getPredeterminada(): ?bool
    {
        return $this->predeterminada;
    }

    public function setPredeterminada(?bool $predeterminada): self
    {
        $this->predeterminada = $predeterminada;

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
