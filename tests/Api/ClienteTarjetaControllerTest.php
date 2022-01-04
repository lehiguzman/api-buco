<?php

namespace App\Tests\Controller;

use App\Tests\BaseControllerTest;

# https://symfony.com/doc/current/testing.html
# https://phpunit.readthedocs.io/en/8.5/assertions.html
class ClienteTarjetaControllerTest extends BaseControllerTest
{
    private $pathModule;

    public function testModuloClienteTarjeta()
    {
        $this->pathModule = "/api/v1/clientes/tarjetas";

        // activar pruebas unitarias de este módulo
        if (MODULO_CLIENTE_TARJETAS) {
            $this->prepare();

            $users = [];
            $users[] = ['_username' => "cliente01", '_password' => "Buco123456!", 'permissions' => '---'];

            foreach ($users as $value) {
                // login user
                $this->loginApi($value);

                # Obtener Listado
                $options = [
                    'endpoint' => "",
                    'pathModule' => $this->pathModule,
                ];
                $this->obtener($options, "list");

                # Registrar
                $options = [
                    'endpoint' => "",
                    'pathModule' => $this->pathModule,
                    'body' => [
                        'nombre' => 'TDC 001',
                        'numero' => '5555444411112222',
                        'fechaExpiracion' => '12/24',
                        'cvv' => '789',
                    ],
                ];
                $registro = $this->registrar($options);
                // $registro = NULL;
                if ($registro && isset($registro->id)) {
                    # Obtener registrado
                    $options = [
                        'pathModule' => $this->pathModule,
                        'endpoint' => "/" . $registro->id,
                    ];
                    $this->obtener($options, "details");

                    # Editar
                    $options = [
                        'endpoint' => "/" . $registro->id,
                        'pathModule' => $this->pathModule,
                        'body' => [
                            'nombre' => 'TDC Editada',
                        ],
                    ];
                    $this->editar($options, $value['permissions']);

                    # Borrar
                    $options = [
                        'endpoint' => "/" . $registro->id,
                        'pathModule' => $this->pathModule,
                    ];
                    $this->eliminar($options, $value['permissions']);
                }
            }
        }

        // retornar true por defecto
        $this->assertTrue(true);
    }

    /**
     * Comprobar campos de la entidad
     */
    private function comprobacioneModulo($registro)
    {
        $this->assertIsInt($registro->id);
        $this->assertNotNull($registro->fechaRegistro);
        $this->assertIsBool($registro->eliminado);
    }

    private function obtener($options, $type)
    {
        $res = $this->getListGenerico($options, $type);
        if (isset($res['response'])) {
            $response = $res['response'];
            if (strcmp($type, "list") === 0) {
                foreach ($response->data as $value) {
                    $this->comprobacioneModulo($value);
                }
            } elseif (strcmp($type, "details") === 0) {
                $this->comprobacioneModulo($response->data);
            }
        }
    }

    private function registrar($options)
    {
        $body = $options['body'];
        $res = $this->postCreateGenerico($options);
        if (isset($res['response'])) {
            $registro = $res['response'];
            $this->comprobacioneModulo($registro);
            $this->assertStringContainsString($body['nombre'], $registro->nombre);
            $this->assertEquals(substr($body['numero'], -4), substr($registro->numero, -4));
            $this->assertEquals("***", $registro->cvv);
            $this->assertStringContainsString($body['fechaExpiracion'], $registro->fechaExpiracion);
            $this->assertNotNull($registro->tokenPayus);
            return $registro;
        }

        return null;
    }

    /*private function registrar($options)
    {
        $body = $options['body'];
        $res = $this->postCreateGenerico($options);
        if (isset($res['response'])) {
            $response = $res['response'];
            $this->assertStringContainsString($body['nombre'], $response->nombre);
            $this->assertEquals(substr($body['numero'], -4), substr($response->numero, -4));
            $this->assertEquals("***", $response->cvv);
            $this->assertStringContainsString($body['fechaExpiracion'], $response->fechaExpiracion);
            $this->assertNotNull($response->tokenPayus);
            return $response;
        }

        return null;
    }*/

    private function editar($options, $permissions = "---")
    {
        $res = $this->putEditGenerico($options, $permissions);
        if (isset($res['response'])) {
            $response = $res['response'];
            $this->assertIsInt($response->id);
        } elseif (isset($res['forbidden'])) {
            $this->assertEquals($permissions, 'forbidden');
        }
    }

    private function eliminar($options, $permissions = "---")
    {
        $res = $this->deleteDataGenerico($options, $permissions);
        if (isset($res['response'])) {
            $response = $res['response'];
            $this->assertTrue($response->eliminado);
            $this->assertIsBool($response->eliminado);
            $this->assertNotNull($response->fechaEliminado);
            if (isset($response->estado)) {
                $this->assertEquals(0, $response->estado);
            }
        } elseif (isset($res['forbidden'])) {
            $this->assertEquals($permissions, 'forbidden');
        }
    }
}
