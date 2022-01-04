<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

# https://symfony.com/doc/current/testing.html
# https://phpunit.readthedocs.io/en/8.5/assertions.html
class ApiDocsControllerTest extends WebTestCase
{
    public function testShowDocs()
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc');

        /** Unicode
         * \u00e1 = á
         * \u00e9 = é
         * \u00ed = í
         * \u00f3 = ó
         * \u00fa = ú
         * \u00c1 = Á
         * \u00c9 = É
         * \u00cd = Í
         * \u00d3 = Ó
         * \u00da = Ú
         * \u00f1 = ñ
         * \u00d1 = Ñ
         **/

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $variable = [
            'Buco',
            'Documentaci\u00f3n API',
            '\u00d3rdenes de Servicios',
            'API Rest',
            'api/v1/',
            'Autenticaci\u00f3n',
            'Auth',
            'Portafolio',
            'Formularios',
            'Formularios Din\u00e1micos',
            'Calificaciones',
            'Clientes',
            'Comisiones',
            'Departamentos',
            'Direcciones',
            'Documentos',
            'Ejemplos',
            'Favoritos',
            'Firebase',
            'M\u00e9todos de Pago',
            'Notificaciones',
            'Ordenes',
            'Portafolio',
            'Profesional',
            'Profesionales',
            'Servicios',
            'Tareas',
            'Tarifas',
            'Tarjetas',
            'Tipos de',
            'Usuarios',
        ];
        foreach ($variable as $key => $value) {
            $this->assertStringContainsStringIgnoringCase($value, $client->getResponse()->getContent(), "No se encontro referencia a: $value");
        }
    }
}
