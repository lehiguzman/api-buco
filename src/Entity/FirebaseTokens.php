<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FirebaseTokens")
 * @ORM\Entity(repositoryClass="App\Repository\FirebaseTokensRepository")
 */
class FirebaseTokens
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Firebase")
     * @ORM\JoinColumn(nullable=false)
     */
    private $firebase;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deviceModel;

    /**
     * @ORM\Column(type="string", length=255, unique=TRUE)
     */
    private $pushToken;

    /**
     * Token para renovar pushToken
     * @ORM\Column(type="text")
     */
    protected $pushRefreshToken = "---";

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirebase(): ?Firebase
    {
        return $this->firebase;
    }

    public function setFirebase(?Firebase $firebase): self
    {
        $this->firebase = $firebase;

        return $this;
    }

    public function getDeviceModel(): ?string
    {
        return $this->deviceModel;
    }

    public function setDeviceModel(?string $deviceModel): self
    {
        $this->deviceModel = $deviceModel;

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

    public function getPushRefreshToken(): ?string
    {
        return $this->pushRefreshToken;
    }

    public function setPushRefreshToken(string $pushRefreshToken): self
    {
        $this->pushRefreshToken = $pushRefreshToken;

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
}
