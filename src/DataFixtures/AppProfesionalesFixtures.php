<?php

namespace App\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\DataFixtures\AppServiciosFixtures;
use App\Entity\Profesional;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 */
class AppProfesionalesFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->encoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $userProf = new User();
        $userProf->setName("Profesional Prueba");
        $userProf->setEmail("profesional01@buco.com");
        $userProf->setUsername("profesional01");
        $passwordC01 = "Buco123456!";
        $userProf->setPlainPassword($passwordC01);
        $userProf->setPassword($this->encoder->encodePassword($userProf, $passwordC01));
        $userProf->setRoles("ROLE_PROFESIONAL");
        $userProf->setFoto("profesional.png");
        $manager->persist($userProf);

        // Servicio
        $Servicio = $manager->getRepository("App:Servicio")->find(1);

        $profesional01 = new Profesional($Servicio);
        $profesional01->setUser($userProf);
        $profesional01->setServicio($Servicio);
        $profesional01->setNombre("Profesional 01");
        $profesional01->setApellido("De Tal");
        $profesional01->setIdentificacion("123456789");
        $profesional01->setNacionalidad("Panameño");
        $profesional01->setTelefono("66668888");
        $profesional01->setDireccion("Calle la Plaza");
        $profesional01->setLatitud(000);
        $profesional01->setLongitud(000);
        $profesional01->setAniosExperiencia(5);
        $profesional01->setDestrezaDetalle("Varias");
        $profesional01->setRadioCobertura(3.0);
        $profesional01->setVehiculo("SI");
        $manager->persist($profesional01);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            AppFixtures::class,
            AppServiciosFixtures::class,
        );
    }
}
