<?php

namespace App\Entity;

use App\Repository\FormularioDinamicoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FormularioDinamico")
 * @ORM\Entity(repositoryClass=FormularioDinamicoRepository::class)
 */
class FormularioDinamico
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80, unique=TRUE)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=20, unique=TRUE)
     */
    private $tipo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activo = TRUE;


    public function __toString()
    {
        return (string) $this->getNombre();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }
}
