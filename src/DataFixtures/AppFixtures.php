<?php

namespace App\DataFixtures;

use App\Entity\ConfigCostoComision;
use App\Entity\FormularioDinamico;
use App\Entity\Profesional;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * https://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html
 */
class AppFixtures extends Fixture
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
        // cargar Usuarios
        $this->loadUsuariosSistema($manager);

        // cargar Clientes
        $this->loadUsuariosClientes($manager);

        // Config Comision de Buconexion
        $this->ConfigCostoComision($manager);

        // Config de Formulario Dinámico
        $this->ConfigFormularioDinamico($manager);

        // Config de Formulario Dinámico
        // $this->ConfigFormularioDinamicoServicio($manager);
    }

    // Usuarios del Sistema
    public function loadUsuariosSistema(ObjectManager $manager)
    {
        $userSuperAamin = new User();
        $userSuperAamin->setName("Super Administrador");
        $userSuperAamin->setEmail("superadmin@strappinc.com");
        $userSuperAamin->setUsername("superadmin");
        $passwordSA = "Str4ppT3ch.,";
        $userSuperAamin->setPlainPassword($passwordSA);
        $userSuperAamin->setPassword($this->encoder->encodePassword($userSuperAamin, $passwordSA));
        $userSuperAamin->setRoles("ROLE_SUPER_ADMIN");
        $userSuperAamin->setFoto("admin.png");
        $manager->persist($userSuperAamin);

        $userAdmin = new User();
        $userAdmin->setName("Administrador");
        $userAdmin->setEmail("admin@buco.com");
        $userAdmin->setUsername("admin");
        $passwordA = "Adm1nSyst3m!";
        $userAdmin->setPlainPassword($passwordA);
        $userAdmin->setPassword($this->encoder->encodePassword($userAdmin, $passwordA));
        $userAdmin->setRoles("ROLE_ADMIN");
        $userAdmin->setFoto("guest.png");
        $manager->persist($userAdmin);

        $userCallCenter = new User();
        $userCallCenter->setName("CallCenter");
        $userCallCenter->setEmail("callcenter@buco.com");
        $userCallCenter->setUsername("callcenter");
        $passwordCC = "CallCenter1!";
        $userCallCenter->setPlainPassword($passwordCC);
        $userCallCenter->setPassword($this->encoder->encodePassword($userCallCenter, $passwordCC));
        $userCallCenter->setRoles("ROLE_CALLCENTER");
        $userCallCenter->setFoto("manager.png");
        $manager->persist($userCallCenter);

        $userInterno = new User();
        $userInterno->setName("API Interno");
        $userInterno->setEmail("apiinterno@buco.com");
        $userInterno->setUsername("apiinterno");
        $passwordAI = "API$123Interno";
        $userInterno->setPlainPassword($passwordAI);
        $userInterno->setPassword($this->encoder->encodePassword($userInterno, $passwordAI));
        $userInterno->setRoles("ROLE_API_INTERNO");
        $userInterno->setFoto("api.png");
        $manager->persist($userInterno);

        $userExterno = new User();
        $userExterno->setName("API Externo");
        $userExterno->setEmail("apiexterno@buco.com");
        $userExterno->setUsername("apiexterno");
        $passwordAE = "API$123Externo";
        $userExterno->setPlainPassword($passwordAE);
        $userExterno->setPassword($this->encoder->encodePassword($userExterno, $passwordAE));
        $userExterno->setRoles("ROLE_API_EXTERNO");
        $userExterno->setFoto("api.png");
        $manager->persist($userExterno);

        $manager->flush();
    }

    // Usuarios Clientes
    public function loadUsuariosClientes(ObjectManager $manager)
    {
        $cliente01 = new User();
        $cliente01->setName("Cliente Prueba");
        $cliente01->setEmail("cliente01@mail.com");
        $cliente01->setUsername("cliente01");
        $passwordC01 = "Buco123456!";
        $cliente01->setPlainPassword($passwordC01);
        $cliente01->setPassword($this->encoder->encodePassword($cliente01, $passwordC01));
        $cliente01->setRoles("ROLE_CLIENTE");
        $cliente01->setFoto("cliente.png");
        $manager->persist($cliente01);

        $manager->flush();
    }

    public function ConfigCostoComision(ObjectManager $manager)
    {
        // Rango 1
        $confCosto1 = new ConfigCostoComision();
        $confCosto1->setRangoA(1);
        $confCosto1->setRangoB(10);
        $confCosto1->setCostoBuconexion(1.50);
        $confCosto1->setPorcentaje(false);
        $confCosto1->setCategoria("Servicio");
        $manager->persist($confCosto1);

        // Rango 2
        $confCosto2 = new ConfigCostoComision();
        $confCosto2->setRangoA(11);
        $confCosto2->setRangoB(25);
        $confCosto2->setCostoBuconexion(3.00);
        $confCosto2->setPorcentaje(false);
        $confCosto2->setCategoria("Servicio");
        $manager->persist($confCosto2);

        // Rango 3
        $confCosto3 = new ConfigCostoComision();
        $confCosto3->setRangoA(26);
        $confCosto3->setRangoB(100);
        $confCosto3->setCostoBuconexion(5.00);
        $confCosto3->setPorcentaje(false);
        $confCosto3->setCategoria("Servicio");
        $manager->persist($confCosto3);

        // Rango 4
        $confCosto4 = new ConfigCostoComision();
        $confCosto4->setRangoA(101);
        $confCosto4->setRangoB(150);
        $confCosto4->setCostoBuconexion(10.00);
        $confCosto4->setPorcentaje(false);
        $confCosto4->setCategoria("Servicio");
        $manager->persist($confCosto4);

        // Rango 5
        $confCosto5 = new ConfigCostoComision();
        $confCosto5->setRangoA(151);
        $confCosto5->setRangoB(300);
        $confCosto5->setCostoBuconexion(15.00);
        $confCosto5->setPorcentaje(false);
        $confCosto5->setCategoria("Servicio");
        $manager->persist($confCosto5);

        // Rango 6
        $confCosto6 = new ConfigCostoComision();
        $confCosto6->setRangoA(301);
        $confCosto6->setRangoB(500);
        $confCosto6->setCostoBuconexion(25.00);
        $confCosto6->setPorcentaje(false);
        $confCosto6->setCategoria("Servicio");
        $manager->persist($confCosto6);

        // Rango 7
        $confCosto7 = new ConfigCostoComision();
        $confCosto7->setRangoA(501);
        $confCosto7->setRangoB(1000);
        $confCosto7->setCostoBuconexion(50.00);
        $confCosto7->setPorcentaje(false);
        $confCosto7->setCategoria("Servicio");
        $manager->persist($confCosto7);

        // Rango 8
        $confCosto8 = new ConfigCostoComision();
        $confCosto8->setRangoA(1001);
        $confCosto8->setRangoB(-1);
        $confCosto8->setCostoBuconexion(10);
        $confCosto8->setPorcentaje(true);
        $confCosto8->setCategoria("Servicio");
        $manager->persist($confCosto8);

        $manager->flush();
    }

    // Config de Formulario Dinámico
    public function ConfigFormularioDinamico(ObjectManager $manager)
    {
        $FDTexto = new FormularioDinamico();
        $FDTexto->setNombre("Campo Tipo Texto");
        $FDTexto->setTipo("text");
        $manager->persist($FDTexto);

        $FDNumerico = new FormularioDinamico();
        $FDNumerico->setNombre("Campo Tipo Númerico");
        $FDNumerico->setTipo("number");
        $manager->persist($FDNumerico);

        $FDNumericoDecimal = new FormularioDinamico();
        $FDNumericoDecimal->setNombre("Campo Tipo Númerico con Decimales");
        $FDNumericoDecimal->setTipo("decimal");
        $manager->persist($FDNumericoDecimal);

        $FDDinero = new FormularioDinamico();
        $FDDinero->setNombre("Campo Tipo Dinero");
        $FDDinero->setTipo("money");
        $manager->persist($FDDinero);

        $FDFecha = new FormularioDinamico();
        $FDFecha->setNombre("Campo Tipo Fecha");
        $FDFecha->setTipo("date");
        $manager->persist($FDFecha);

        $FDHora = new FormularioDinamico();
        $FDHora->setNombre("Campo Tipo Hora");
        $FDHora->setTipo("time");
        $manager->persist($FDHora);

        $FDFechaHora = new FormularioDinamico();
        $FDFechaHora->setNombre("Campo Tipo Fecha Hora");
        $FDFechaHora->setTipo("datetime");
        $manager->persist($FDFechaHora);

        $FDSiNo = new FormularioDinamico();
        $FDSiNo->setNombre("Campo Tipo Si o No");
        $FDSiNo->setTipo("boolean");
        $manager->persist($FDSiNo);

        $FDSeleccionSimple = new FormularioDinamico();
        $FDSeleccionSimple->setNombre("Campo Tipo Selección Simple");
        $FDSeleccionSimple->setTipo("selectsimple");
        $manager->persist($FDSeleccionSimple);

        $FDSeleccionMultiple = new FormularioDinamico();
        $FDSeleccionMultiple->setNombre("Campo Tipo Selección Múltiple");
        $FDSeleccionMultiple->setTipo("selectmultiple");
        $manager->persist($FDSeleccionMultiple);

        $manager->flush();
    }

    // Config de Formulario Dinámico
    public function ConfigFormularioDinamicoServicio(ObjectManager $manager)
    {
        // $FDServicio = new FormularioDinamicoServicio();
        // $FDServicio->setNombre("Campo Tipo Texto");
        // $FDServicio->setTipo("text");
        // $manager->persist($FDServicio);

        // $manager->flush();
    }
}
