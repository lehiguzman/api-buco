<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="OrdenServicio")
 * @ORM\Entity(repositoryClass="App\Repository\OrdenServicioRepository")
 */
class OrdenServicio
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servicio")
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id")     
     */
    private $servicio;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaHora;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MetodoPago")
     * @ORM\JoinColumn(name="metodo_pago_id", referencedColumnName="id")
     */
    private $metodoPago;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MetodoPagoCliente")
     * @ORM\JoinColumn(name="metodo_pago_cliente_id", referencedColumnName="id", nullable=true)
     */
    private $metodoPagoCliente;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ClienteTarjetas")
     * @ORM\JoinColumn(name="cliente_tarjeta_id", referencedColumnName="id", nullable=true)
     */
    private $clienteTarjeta = NULL;

    /**
     * @ORM\Column(type="float")
     */
    private $latitud;

    /**
     * @ORM\Column(type="float")
     */
    private $longitud;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @ORM\Column(type="integer")
     */
    private $estatus = 1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $monto = 0.0;

    /**
     * Costo por Cancelar ODS
     * @ORM\Column(type="float")
     */
    private $montoPenalizacion = 0.0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $porcentajeBackup = 0.0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $comision = 0.0;

    /**
     * @ORM\Column(type="float")
     */
    private $comisionBuconexion = 0.0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $montoEfectivo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cantidadProfesionales;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado = NULL;

    /**
     * Fecha de creación
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    protected $createdAt;

    /**
     * Fecha de actualización
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    protected $updatedAt = NULL;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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

    public function getFechaHora(): ?\DateTimeInterface
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTimeInterface $fechaHora): self
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getMetodoPago(): ?MetodoPago
    {
        return $this->metodoPago;
    }

    public function setMetodoPago(?MetodoPago $metodoPago): self
    {
        $this->metodoPago = $metodoPago;

        return $this;
    }

    public function getMetodoPagoCliente(): ?MetodoPagoCliente
    {
        return $this->metodoPagoCliente;
    }

    public function setMetodoPagoCliente(?MetodoPagoCliente $metodoPagoCliente): self
    {
        $this->metodoPagoCliente = $metodoPagoCliente;

        return $this;
    }

    public function getClienteTarjeta(): ?ClienteTarjetas
    {
        return $this->clienteTarjeta;
    }

    public function setClienteTarjeta(?ClienteTarjetas $clienteTarjeta): self
    {
        $this->clienteTarjeta = $clienteTarjeta;

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

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * @return string
     */
    public function getEstatusTexto(): ?string
    {
        switch ($this->getEstatus()) {
            default:
            case 0:
                $text = 'Cancelada';
                break;
            case 1:
                $text = 'En Espera';
                break;
            case 2:
                $text = 'Confirmada';
                break;
            case 3:
                $text = 'Rechazada';
                break;
            case 4:
                $text = 'Iniciada';
                break;
            case 5:
                $text = 'Pendiente por Aprobación';
                break;
            case 6:
                $text = 'Pendiente de Pago';
                break;
            case 7:
                $text = 'Pagada';
                break;
            case 8:
                $text = 'Calificada';
                break;
        }

        return $text;
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

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): self
    {
        $this->titulo = $titulo;

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

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getMonto(): ?float
    {
        return $this->monto;
    }

    public function setMonto(?float $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getMontoPenalizacion(): ?float
    {
        return $this->montoPenalizacion;
    }

    public function setMontoPenalizacion(float $montoPenalizacion): self
    {
        $this->montoPenalizacion = $montoPenalizacion;

        return $this;
    }

    public function getPorcentajeBackup(): ?float
    {
        return $this->porcentajeBackup;
    }

    public function setPorcentajeBackup(?float $porcentajeBackup): self
    {
        $this->porcentajeBackup = $porcentajeBackup;

        return $this;
    }

    public function getComision(): ?float
    {
        return $this->comision;
    }

    public function setComision(?float $comision): self
    {
        $this->comision = $comision;

        return $this;
    }

    public function getComisionBuconexion(): ?float
    {
        return $this->comisionBuconexion;
    }

    public function setComisionBuconexion(float $comisionBuconexion): self
    {
        $this->comisionBuconexion = $comisionBuconexion;

        return $this;
    }

    public function getMontoEfectivo(): ?float
    {
        return $this->montoEfectivo;
    }

    public function setMontoEfectivo(?float $montoEfectivo): self
    {
        $this->montoEfectivo = $montoEfectivo;

        return $this;
    }

    public function getCantidadProfesionales(): ?int
    {
        return $this->cantidadProfesionales;
    }

    public function setCantidadProfesionales(?int $cantidadProfesionales): self
    {
        $this->cantidadProfesionales = $cantidadProfesionales;

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

    public function getFechaEliminado(): ?\DateTimeInterface
    {
        return $this->fechaEliminado;
    }

    public function setFechaEliminado(?\DateTimeInterface $fechaEliminado): self
    {
        $this->fechaEliminado = $fechaEliminado;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
