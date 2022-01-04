<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ProfesionalMetodoPago")
 * @ORM\Entity(repositoryClass="App\Repository\ProfesionalMetodoPagoRepository")
 */
class ProfesionalMetodoPago
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
    private $profesional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MetodoPago")
     * @ORM\JoinColumn(name="metodo_pago_id", referencedColumnName="id")
     */
    private $metodoPago;

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

    public function getMetodoPago(): ?MetodoPago
    {
        return $this->metodoPago;
    }

    public function setMetodoPago(?MetodoPago $metodoPago): self
    {
        $this->metodoPago = $metodoPago;

        return $this;
    }
}
