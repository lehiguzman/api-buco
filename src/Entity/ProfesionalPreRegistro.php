<?php

namespace App\Entity;

use App\Repository\ProfesionalPreRegistroRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ProfesionalPreRegistro")
 * @ORM\Entity(repositoryClass=ProfesionalPreRegistroRepository::class)
 */
class ProfesionalPreRegistro
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servicio")
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id")
     */
    protected $servicio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Direccion")
     * @ORM\JoinColumn(name="direccion_id", referencedColumnName="id")
     */
    protected $direccion;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombreCompleto = "";

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $cedula = "";

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $tlfCelular = "";

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $correo = "";

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $fechaNacimiento = "";

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $genero = "femenino";

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $nacionalidad = "";

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $redesSociales = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $especialidad = "";

    /**
     * @ORM\Column(type="integer")
     */
    private $areaCobertura = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $aniosExperiencia = 0;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $vehiculo = "no";

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $metodosPago = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $camposEspecificos = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $tareasTarifas = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $documentos = [];

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $justificacion = "";

    /**
     * Estado [0: Rechazado, 1: NoCompleto, 2:Completado, 3: Aprobado]
     * @ORM\Column(type="integer")
     */
    private $estado = 1;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaActualizado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCompletado = NULL;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaAprobado = NULL;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRechazado = NULL;


    public function __construct()
    {
        $this->fechaRegistro = new \DateTime();
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

    public function getDireccion(): ?Direccion
    {
        return $this->direccion;
    }

    public function setDireccion(?Direccion $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getNombreCompleto(): ?string
    {
        return $this->nombreCompleto;
    }

    public function setNombreCompleto(string $nombreCompleto): self
    {
        $this->nombreCompleto = $nombreCompleto;

        return $this;
    }

    public function getCedula(): ?string
    {
        return $this->cedula;
    }

    public function setCedula(string $cedula): self
    {
        $this->cedula = $cedula;

        return $this;
    }

    public function getTlfCelular(): ?string
    {
        return $this->tlfCelular;
    }

    public function setTlfCelular(string $tlfCelular): self
    {
        $this->tlfCelular = $tlfCelular;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getFechaNacimiento(): ?string
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(string $fechaNacimiento): self
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function setGenero(string $genero): self
    {
        $this->genero = $genero;

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

    public function getAreaCobertura(): ?int
    {
        return $this->areaCobertura;
    }

    public function setAreaCobertura(int $areaCobertura): self
    {
        $this->areaCobertura = $areaCobertura;

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

    public function getRedesSociales(): ?array
    {
        return $this->redesSociales;
    }

    public function setRedesSociales(?array $redesSociales): self
    {
        $this->redesSociales = $redesSociales;

        return $this;
    }

    public function getEspecialidad(): ?string
    {
        return $this->especialidad;
    }

    public function setEspecialidad(string $especialidad): self
    {
        $this->especialidad = $especialidad;

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

    public function getCamposEspecificos(): ?array
    {
        return $this->camposEspecificos;
    }

    public function setCamposEspecificos(?array $camposEspecificos): self
    {
        $this->camposEspecificos = $camposEspecificos;

        return $this;
    }

    public function getTareasTarifas(): ?array
    {
        return $this->tareasTarifas;
    }

    public function setTareasTarifas(?array $tareasTarifas): self
    {
        $this->tareasTarifas = $tareasTarifas;

        return $this;
    }

    public function getDocumentos(): ?array
    {
        return $this->documentos;
    }

    public function setDocumentos(?array $documentos): self
    {
        $this->documentos = $documentos;

        return $this;
    }

    public function getMetodosPago(): ?array
    {
        return $this->metodosPago;
    }

    public function setMetodosPago(?array $metodosPago): self
    {
        $this->metodosPago = $metodosPago;

        return $this;
    }

    public function getJustificacion(): ?string
    {
        return $this->justificacion;
    }

    public function setJustificacion(string $justificacion): self
    {
        $this->justificacion = $justificacion;

        return $this;
    }

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(int $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return string
     */
    public function getEstadoTexto(): ?string
    {
        switch ($this->getEstado()) {
            case 0:
                $text = 'Rechazado';
                break;
            case 1:
                $text = 'No Completo';
                break;
            case 2:
                $text = 'Completado';
                break;
            case 3:
                $text = 'Aprobado';
                break;
        }

        return $text;
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

    public function getFechaActualizado(): ?\DateTimeInterface
    {
        return $this->fechaActualizado;
    }

    public function setFechaActualizado(): self
    {
        $this->fechaActualizado = new \DateTime();

        return $this;
    }

    public function getFechaCompletado(): ?\DateTimeInterface
    {
        return $this->fechaCompletado;
    }

    public function setFechaCompletado(): self
    {
        $this->fechaCompletado = new \DateTime();

        return $this;
    }

    public function getFechaAprobado(): ?\DateTimeInterface
    {
        return $this->fechaAprobado;
    }

    public function setFechaAprobado(): self
    {
        $this->fechaAprobado = new \DateTime();

        return $this;
    }

    public function getFechaRechazado(): ?\DateTimeInterface
    {
        return $this->fechaRechazado;
    }

    public function setFechaRechazado(): self
    {
        $this->fechaRechazado = new \DateTime();

        return $this;
    }
}
