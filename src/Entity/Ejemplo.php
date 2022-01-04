<?php

namespace App\Entity;

# https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Table(name="Ejemplo")
 * @ORM\Entity(repositoryClass="App\Repository\EjemploRepository")
 * @ExclusionPolicy("all")
 */
class Ejemplo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $id;

    /**
     * Descripción campo1
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $campo1;

    /**
     * Descripción campo2
     * @ORM\Column(type="string", length=255)
     * @Expose()
     */
    private $campo2;

    /**
     * Estado [0:Inactivo, 1:Activo]
     * @ORM\Column(type="integer")
     * @Expose()
     */
    private $estado = 1;

    /**
     * @ORM\Column(type="datetime")
     * @Expose()
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
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampo1(): ?string
    {
        return $this->campo1;
    }

    public function setCampo1(string $campo1): self
    {
        $this->campo1 = $campo1;

        return $this;
    }

    public function getCampo2(): ?string
    {
        return $this->campo2;
    }

    public function setCampo2(string $campo2): self
    {
        $this->campo2 = $campo2;

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
            default:
                $text = 'No Indicado';
                break;
            case 0:
                $text = 'Inactivo';
                break;
            case 1:
                $text = 'ACtivo';
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
