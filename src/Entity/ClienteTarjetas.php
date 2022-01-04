<?php

namespace App\Entity;

use App\Repository\ClienteTarjetasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ClienteTarjetas")
 * @ORM\Entity(repositoryClass=ClienteTarjetasRepository::class)
 */
class ClienteTarjetas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="cliente_id", referencedColumnName="id")
     */
    private $cliente;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cvv;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tokenPayus;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $fechaExpiracion;

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

    public function getCliente(): ?User
    {
        return $this->cliente;
    }

    public function setCliente(?User $cliente): self
    {
        $this->cliente = $cliente;

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

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

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

    public function getTokenPayus(): ?string
    {
        return $this->tokenPayus;
    }

    public function setTokenPayus(string $tokenPayus): self
    {
        $this->tokenPayus = $tokenPayus;

        return $this;
    }

    public function getFechaExpiracion(): ?string
    {
        return $this->fechaExpiracion;
    }

    public function setFechaExpiracion(string $fechaExpiracion): self
    {
        $this->fechaExpiracion = $fechaExpiracion;

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
