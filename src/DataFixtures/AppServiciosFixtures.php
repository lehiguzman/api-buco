<?php

namespace App\DataFixtures;

use App\Entity\Servicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 */
class AppServiciosFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $servicio01 = new Servicio();
        $servicio01->setNombre("Servicio 1 BS");
        $servicio01->setDescripcion("Servicio de Buco Servicio");
        $servicio01->setMontoComision(7.00);
        $manager->persist($servicio01);

        $servicio02 = new Servicio();
        $servicio02->setNombre("Servicio 2 BT");
        $servicio02->setDescripcion("Servicio de Buco Talento");
        $servicio02->setSistemaTipo(2);
        $servicio02->setComisionTipo(2);
        $servicio02->setMontoComision(10.00);
        $manager->persist($servicio02);


        $manager->flush();
    }
}
