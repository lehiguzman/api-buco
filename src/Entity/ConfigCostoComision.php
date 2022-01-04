<?php

namespace App\Entity;

use App\Repository\ConfigCostoComisionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ConfigCostoComision")
 * @ORM\Entity(repositoryClass=ConfigCostoComisionRepository::class)
 */
class ConfigCostoComision
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $rangoA;

    /**
     * @ORM\Column(type="integer")
     */
    private $rangoB;

    /**
     * @ORM\Column(type="float")
     */
    private $costoBuconexion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $porcentaje;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categoria;

    /**
     * Tipo Sistema [1:bucoservicio - 2:bucotalento]
     * @ORM\Column(type="integer")
     */
    private $sistemaTipo = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRangoA(): ?int
    {
        return $this->rangoA;
    }

    public function setRangoA(int $rangoA): self
    {
        $this->rangoA = $rangoA;

        return $this;
    }

    public function getRangoB(): ?int
    {
        return $this->rangoB;
    }

    public function setRangoB(int $rangoB): self
    {
        $this->rangoB = $rangoB;

        return $this;
    }

    public function getCostoBuconexion(): ?float
    {
        return $this->costoBuconexion;
    }

    public function setCostoBuconexion(float $costoBuconexion): self
    {
        $this->costoBuconexion = $costoBuconexion;

        return $this;
    }

    public function getPorcentaje(): ?bool
    {
        return $this->porcentaje;
    }

    public function setPorcentaje(bool $porcentaje): self
    {
        $this->porcentaje = $porcentaje;

        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;

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
}
