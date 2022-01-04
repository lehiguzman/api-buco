<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Firebase")
 * @ORM\Entity(repositoryClass="App\Repository\FirebaseRepository")
 */
class Firebase
{
    /**
     * Identificador único del registro
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    private $user;

    /**
     * UID del usuario
     * @ORM\Column(type="string", length=255, unique=TRUE)
     */
    protected $uid;

    /**
     * Token para Push Notification
     * @ORM\Column(type="text")
     */
    protected $pushToken = "---";

    /**
     * Id del token para eliminación
     * @ORM\Column(type="text", nullable=true)
     */
    private $idToken;

    /**
     * Token para recuperar acceso
     * @ORM\Column(type="text")
     */
    protected $refreshToken = "---";

    /**
     * Fecha de registro
     * @ORM\Column(type="datetime")
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPushToken(): ?string
    {
        return $this->pushToken;
    }

    public function setPushToken(string $pushToken): self
    {
        $this->pushToken = $pushToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getIdToken(): ?string
    {
        return $this->idToken;
    }

    public function setIdToken(?string $idToken): self
    {
        $this->idToken = $idToken;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
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
