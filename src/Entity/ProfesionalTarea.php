<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ProfesionalTarea")
 * @ORM\Entity(repositoryClass="App\Repository\ProfesionalTareaRepository")
 */
class ProfesionalTarea
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profesional")
     * @ORM\JoinColumn(name="profesional_id", referencedColumnName="id")
     */
    protected $profesional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tarea")
     * @ORM\JoinColumn(name="tarea_id", referencedColumnName="id")
     */
    protected $tarea;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $precio = 0.0;

    /**
     * @ORM\Column(type="integer")
     */
    private $estado = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfesional(): ?Profesional
    {
        return $this->profesional;
    }

    public function setProfesional(?Profesional $profesional): self
    {
        $this->profesional = $profesional;

        return $this;
    }

    public function getTarea(): ?Tarea
    {
        return $this->tarea;
    }

    public function setTarea(?Tarea $tarea): self
    {
        $this->tarea = $tarea;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(?float $precio): self
    {
        $this->precio = $precio;

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
                $text = 'Inactiva';
                break;
            case 1:
                $text = 'Activa';
                break;
        }

        return $text;
    }
}
