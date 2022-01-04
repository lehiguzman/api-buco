<?php

namespace App\Tests\Controller;

use App\Tests\BaseControllerTest;

# https://symfony.com/doc/current/testing.html
# https://phpunit.readthedocs.io/en/8.5/assertions.html
class EjemploControllerTest extends BaseControllerTest
{
    private $pathModule;

    public function testModuloEjemplo()
    {
        $this->pathModule = "/api/v1/ejemplos";

        // activar pruebas unitarias de este módulo
        if (MODULO_EJEMPLO) {
            $this->prepare();

            $users = [];
            $users[] = ['_username' => "superadmin", '_password' => "Str4ppT3ch.,", 'permissions' => '---'];

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
                $numRand = rand(1, 999);
                $options = [
                    'endpoint' => "",
                    'pathModule' => $this->pathModule,
                    'body' => [
                        'campo1' => "Campo 1 $numRand",
                        'campo2' => "Campo 2 $numRand",
                    ],
                ];
                $registro = $this->registrar($options);
                // $registro = NULL;
                if ($registro && isset($registro->id)) {
                    # Obtener
                    $options = [
                        'pathModule' => $this->pathModule,
                        'endpoint' => "/" . $registro->id,
                    ];
                    $this->obtener($options, "details");

                    # Editar
                    $numRand = rand(1, 999);
                    $options = [
                        'endpoint' => "/" . $registro->id,
                        'pathModule' => $this->pathModule,
                        'body' => [
                            'campo1' => "(editado) Campo 1 $numRand",
                            'campo2' => "(editado) Campo 2 $numRand",
                        ],
                    ];
                    $this->editar($options);

                    # Borrar
                    $options = [
                        'endpoint' => "/" . $registro->id,
                        'pathModule' => $this->pathModule,
                    ];
                    $this->eliminar($options);
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
        $this->assertIsObject($registro);
        $this->assertIsInt($registro->id);
        $this->assertIsInt($registro->estado);
        $this->assertNotEmpty($registro->campo1);
        $this->assertNotEmpty($registro->campo2);
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
            $this->assertEquals(1, $registro->estado);
            $this->assertNull($registro->fechaEliminado);
            return $registro;
        }

        return null;
    }

    private function editar($options)
    {
        $body = $options['body'];
        $res = $this->putEditGenerico($options);
        if (isset($res['response'])) {
            $registro = $res['response'];
            $this->comprobacioneModulo($registro);
            $this->assertNull($registro->fechaEliminado);
        }
    }

    private function eliminar($options)
    {
        $res = $this->deleteDataGenerico($options);
        if (isset($res['response'])) {
            $registro = $res['response'];
            $this->comprobacioneModulo($registro);
            $this->assertEquals(0, $registro->estado);
            $this->assertNotNull($registro->fechaEliminado);
            $this->assertTrue($registro->eliminado);
        }
    }
}
