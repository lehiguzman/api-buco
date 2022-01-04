<?php

namespace App\Entity;

# https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html
use Doctrine\ORM\Mapping as ORM;

use DateTime;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Table(name="User");
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository");
 * @ORM\HasLifecycleCallbacks()
 */
class User implements AdvancedUserInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Nombre del usuario
     * @ORM\Column(type="string", length=150)
     */
    protected $name;

    /**
     * Correo del usuario
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * Usuario
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $username;

    /**
     * Clave
     * @ORM\Column(type="string", length=255)
     * @Serializer\Exclude()
     */
    protected $password;

    /**
     * Roles
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    protected $roles = [];

    /**
     * Fecha de creación
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * Fecha de actualización
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * Atributo requerido para autenticación
     * @var string
     */
    protected $plainPassword;

    /**
     * Atributo requerido para autenticación
     */
    protected $salt;

    // ################### RECUPERAR CONTRASEÑA ###################
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Exclude()
     */
    private $passwordKey;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordDate;
    // ###################  ###################

    /**
     * Género del Usuario [2:Masculino - 1:Femenino]
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $genero = 1;

    /**
     * Fecha de Nacimiento del Usuario
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaNacimiento;

    /**
     * Foto de Perfil del Usuario
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $foto;

    /**
     * Estatus del Usuario [0:Inactivo - 1:Activo]     
     *
     * @ORM\Column(type="boolean")
     */
    private $isActive = 1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $eliminado = false;

    /**
     * Fecha de eliminado del usuario
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaEliminado = NULL;

    /**
     * Contraseña encriptada asincronamente mendiante el service cryptoJS
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Exclude()
     */
    private $passwordEncrypted = NULL;


    public function __construct()
    {
        $this->isActive = true;
        $this->roles = "ROLE_USER";
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setRoles($roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): ?array
    {
        return array($this->roles);
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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $dateTimeNow = new DateTime('now');
        $this->setUpdatedAt($dateTimeNow);
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt($dateTimeNow);
        }
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        $this->password = null;
    }

    /**
     * Funcion requerida para autenticación
     */
    public function getSalt()
    { }

    /**
     * Funcion requerida para autenticación
     */
    public function eraseCredentials()
    { }

    public function getPasswordKey(): ?string
    {
        return $this->passwordKey;
    }

    public function setPasswordKey(?string $passwordKey): self
    {
        $this->passwordKey = $passwordKey;

        return $this;
    }

    public function getPasswordDate(): ?\DateTimeInterface
    {
        return $this->passwordDate;
    }

    public function setPasswordDate(?\DateTimeInterface $passwordDate): self
    {
        $this->passwordDate = $passwordDate;

        return $this;
    }

    public function setGenero(?int $genero): self
    {
        $this->genero = $genero;

        return $this;
    }

    public function getGenero(): ?int
    {
        return $this->genero;
    }

    /**
     * Retorna el genero en texto
     * @return string
     */
    public function getGeneroTexto()
    {
        switch ($this->getGenero()) {
            case 1:
            default:
                $text = 'Femenino';
                break;
            case 2:
                $text = 'Masculino';
                break;
        }
        return $text;
    }

    /**
     * Retorna un random en string
     * @return string
     */
    public function getRandomStr($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fechaNacimiento): self
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function setFoto(?string $foto): self
    {
        $this->foto = $foto;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    //Funciones abstractas de AdvancedUserInterface
    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function setFechaEliminado(?\DateTimeInterface $fechaEliminado): self
    {
        $this->fechaEliminado = $fechaEliminado;

        return $this;
    }

    public function getFechaEliminado(): ?\DateTimeInterface
    {
        return $this->fechaEliminado;
    }

    public function getPasswordEncrypted(): ?string
    {
        return $this->passwordEncrypted;
    }

    public function setPasswordEncrypted(?string $passwordEncrypted): self
    {
        $this->passwordEncrypted = $passwordEncrypted;

        return $this;
    }
}
