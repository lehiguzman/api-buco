<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="MetodoPagoCliente")
 * @ORM\Entity(repositoryClass="App\Repository\MetodoPagoClienteRepository")
 * @ExclusionPolicy("all")
 */
class MetodoPagoCliente
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
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $numeroTarjeta;

    /**
     * @ORM\Column(type="string",  nullable=true, length=5)
     * @Expose()
     */
    private $mesAnioExpiracion;

    /**
     * @ORM\Column(type="string",  length=3)
     * @Expose()
     */
    private $cvv;

    /**
     * @ORM\Column(type="string", length=80)
     * @Expose()
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Expose()
     */
    private $status;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     * @Expose()
     */
    private $token;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $eliminado = false;

    /**
     * Fecha de creación
     * @ORM\Column(type="datetime", nullable=TRUE)
     * @Expose()
     */
    protected $createdAt;

    /**
     * Fecha de actualización
     * @ORM\Column(type="datetime", nullable=TRUE)
     * @Expose()
     */
    protected $updatedAt = NULL;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getNumeroTarjeta(): ?string
    {
        return $this->numeroTarjeta;
    }

    public function setNumeroTarjeta(string $numeroTarjeta): self
    {
        $this->numeroTarjeta = $numeroTarjeta;

        return $this;
    }

    public function getMesAnioExpiracion(): ?string
    {
        return $this->mesAnioExpiracion;
    }

    public function setMesAnioExpiracion(?string $mesAnioExpiracion): self
    {
        $this->mesAnioExpiracion = $mesAnioExpiracion;

        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): self
    {
        $this->cvv = $cvv;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

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
