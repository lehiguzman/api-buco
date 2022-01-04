<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ServicioDepartamento")
 * @ORM\Entity(repositoryClass="App\Repository\ServicioDepartamentoRepository")
 */
class ServicioDepartamento
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servicio")
     * @ORM\JoinColumn(name="servicio_id", referencedColumnName="id")
     */
    protected $servicio;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Departamento")
     * @ORM\JoinColumn(name="departamento_id", referencedColumnName="id")
     */
    protected $departamento;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDepartamento(): ?Departamento
    {
        return $this->departamento;
    }

    public function setDepartamento(?Departamento $departamento): self
    {
        $this->departamento = $departamento;

        return $this;
    }
}
