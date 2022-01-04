<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ServicioTipoDocumento")
 * @ORM\Entity(repositoryClass="App\Repository\ServicioTipoDocumentoRepository")
 */
class ServicioTipoDocumento
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
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoDocumento")
     * @ORM\JoinColumn(name="tipo_documento_id", referencedColumnName="id")
     */
    protected $tipoDocumento;

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

    public function getTipoDocumento(): ?TipoDocumento
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(?TipoDocumento $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;

        return $this;
    }
}
