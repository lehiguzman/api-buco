<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Profesional")
 * @ORM\Entity(repositoryClass="App\Repository\ProfesionalRepository")
 * @ExclusionPolicy("all")
 */
class Profesional
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
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servicio")
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id")
     * @Expose()
     */
    protected $servicio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Comision")
     * @ORM\JoinColumn(name="comision_id", referencedColumnName="id")
     * @Expose()
     */
    private $comision;

    /**
     * @ORM\Column(type="string", length=45)
     * @Expose()
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=45)
     * @Expose()
     */
    private $apellido;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Expose()
     */
    private $identificacion;

    /**
     * @ORM\Column(type="string", length=45)
     * @Expose()
     */
    private $nacionalidad;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $telefono = "";

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $direccion;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $latitud;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $longitud;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Expose()
     */
    private $destrezaDetalle;

    /**
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $aniosExperiencia = 0;

    /**
     * @ORM\Column(type="float")
     * @Expose()
     */
    private $radioCobertura = 0;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $redesSociales = [];

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $vehiculo = "no";

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $datosEspecificos = [];

    /**
     * Opciones: 1: Corriente, 2: Ahorros
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $tipoCuenta;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Expose()
     */
    private $banco;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Expose()
     */
    private $cuentaBancaria;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $promedioPuntualidad;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $promedioServicio;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $promedioPresencia;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $promedioConocimiento;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Expose()
     */
    private $promedioRecomendado;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $ordenesRechazadas = 0;

    /**
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $estatus = 1;

    /**
     * Fecha de creación
     * @ORM\Column(type="datetime")
     * @Expose()
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPenalizadoInicio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaPenalizadoFin;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return (string) $this->getId() . "  " . $this->getNombre() . " " . $this->getApellido();
    }

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

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getComision(): ?Comision
    {
        return $this->comision;
    }

    public function setComision(?Comision $comision): self
    {
        $this->comision = $comision;

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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getNombreCompleto()
    {
        return (string) $this->getNombre() . " " . $this->getApellido();
    }

    public function getIdentificacion(): ?string
    {
        return $this->identificacion;
    }

    public function setIdentificacion(?string $identificacion): self
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    public function getNacionalidad(): ?string
    {
        return $this->nacionalidad;
    }

    public function setNacionalidad(string $nacionalidad): self
    {
        $this->nacionalidad = $nacionalidad;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getLatitud(): ?float
    {
        return $this->latitud;
    }

    public function setLatitud(?float $latitud): self
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(?float $longitud): self
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getDestrezaDetalle(): ?string
    {
        return $this->destrezaDetalle;
    }

    public function setDestrezaDetalle(?string $destrezaDetalle): self
    {
        $this->destrezaDetalle = $destrezaDetalle;

        return $this;
    }

    public function getAniosExperiencia(): ?int
    {
        return $this->aniosExperiencia;
    }

    public function setAniosExperiencia(int $aniosExperiencia): self
    {
        $this->aniosExperiencia = $aniosExperiencia;

        return $this;
    }

    public function getRadioCobertura(): ?float
    {
        return $this->radioCobertura;
    }

    public function setRadioCobertura(float $radioCobertura): self
    {
        $this->radioCobertura = $radioCobertura;

        return $this;
    }

    public function getRedesSociales(): ?array
    {
        return $this->redesSociales;
    }

    public function setRedesSociales(?array $redesSociales): self
    {
        $this->redesSociales = $redesSociales;

        return $this;
    }

    public function getVehiculo(): ?string
    {
        return $this->vehiculo;
    }

    public function setVehiculo(string $vehiculo): self
    {
        $this->vehiculo = $vehiculo;

        return $this;
    }

    public function getDatosEspecificos(): ?array
    {
        return $this->datosEspecificos;
    }

    public function setDatosEspecificos(?array $datosEspecificos): self
    {
        $this->datosEspecificos = $datosEspecificos;

        return $this;
    }

    public function getTipoCuenta(): ?int
    {
        return $this->tipoCuenta;
    }

    public function setTipoCuenta(?int $tipoCuenta): self
    {
        $this->tipoCuenta = $tipoCuenta;

        return $this;
    }

    public function getTipoCuentaTexto(): ?string
    {
        switch ($this->getTipoCuenta()) {
            default:
                $text = 'No Indicada';
                break;
            case 1:
                $text = 'Corriente';
                break;
            case 2:
                $text = 'Ahorros';
                break;
        }

        return $text;
    }

    public function getBanco(): ?string
    {
        return $this->banco;
    }

    public function setBanco(?string $banco): self
    {
        $this->banco = $banco;

        return $this;
    }

    public function getCuentaBancaria(): ?string
    {
        return $this->cuentaBancaria;
    }

    public function setCuentaBancaria(?string $cuentaBancaria): self
    {
        $this->cuentaBancaria = $cuentaBancaria;

        return $this;
    }

    public function getPromedioPuntualidad(): ?float
    {
        return $this->promedioPuntualidad;
    }

    public function setPromedioPuntualidad(?float $promedioPuntualidad): self
    {
        $this->promedioPuntualidad = $promedioPuntualidad;

        return $this;
    }

    public function getPromedioServicio(): ?float
    {
        return $this->promedioServicio;
    }

    public function setPromedioServicio(?float $promedioServicio): self
    {
        $this->promedioServicio = $promedioServicio;

        return $this;
    }

    public function getPromedioPresencia(): ?float
    {
        return $this->promedioPresencia;
    }

    public function setPromedioPresencia(?float $promedioPresencia): self
    {
        $this->promedioPresencia = $promedioPresencia;

        return $this;
    }

    public function getPromedioConocimiento(): ?float
    {
        return $this->promedioConocimiento;
    }

    public function setPromedioConocimiento(?float $promedioConocimiento): self
    {
        $this->promedioConocimiento = $promedioConocimiento;

        return $this;
    }

    public function getPromedioRecomendado(): ?float
    {
        return $this->promedioRecomendado;
    }

    public function setPromedioRecomendado(?float $promedioRecomendado): self
    {
        $this->promedioRecomendado = $promedioRecomendado;

        return $this;
    }

    public function getOrdenesRechazadas(): ?int
    {
        return $this->ordenesRechazadas;
    }

    public function setOrdenesRechazadas(?int $ordenesRechazadas): self
    {
        $this->ordenesRechazadas = $ordenesRechazadas;

        return $this;
    }

    public function getEstatus(): ?int
    {
        return $this->estatus;
    }

    public function setEstatus(int $estatus): self
    {
        $this->estatus = $estatus;

        return $this;
    }

    public function getEstatusTexto(): ?string
    {
        switch ($this->getEstatus()) {
            default:
            case 0:
                $text = 'Inhabilitado';
                break;
            case 1:
                $text = 'Disponible';
                break;
            case 2:
                $text = 'Penalizado';
                break;
            case 3:
                $text = 'Ocupado';
                break;
            case 4:
                $text = 'Desconectado';
                break;
        }

        return $text;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getFechaPenalizadoInicio(): ?\DateTimeInterface
    {
        return $this->fechaPenalizadoInicio;
    }

    public function setFechaPenalizadoInicio(?\DateTimeInterface $fechaPenalizadoInicio): self
    {
        $this->fechaPenalizadoInicio = $fechaPenalizadoInicio;

        return $this;
    }

    public function getFechaPenalizadoFin(): ?\DateTimeInterface
    {
        return $this->fechaPenalizadoFin;
    }

    public function setFechaPenalizadoFin(?\DateTimeInterface $fechaPenalizadoFin): self
    {
        $this->fechaPenalizadoFin = $fechaPenalizadoFin;

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
