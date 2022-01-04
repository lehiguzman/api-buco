<?php

namespace App\Tests\Controller;

use App\Tests\BaseControllerTest;

# https://symfony.com/doc/current/testing.html
# https://phpunit.readthedocs.io/en/8.5/assertions.html
class ApiAuthControllerTest extends BaseControllerTest
{
    public function testLoginUsers()
    {
        // activar pruebas unitarias de este módulo
        if (MODULO_AUTH) {
            $this->prepare();

            $users = [];
            $users[] = ['_username' => "superadmin", '_password' => "Str4ppT3ch.,", 'permissions' => '---'];
            $users[] = ['_username' => "admin", '_password' => "Adm1nSyst3m!", 'permissions' => '---'];
            $users[] = ['_username' => "callcenter", '_password' => "CallCenter1!", 'permissions' => '---'];
            $users[] = ['_username' => "apiinterno", '_password' => "API$123Interno", 'permissions' => '---'];
            $users[] = ['_username' => "apiexterno", '_password' => "API$123Externo", 'permissions' => '---'];
            $users[] = ['_username' => "cliente01", '_password' => "Buco123456!", 'permissions' => '---'];
            $users[] = ['_username' => "profesional01", '_password' => "Buco123456!", 'permissions' => '---'];

            foreach ($users as $key => $value) {
                // login user
                $this->loginApi($value);
            }

            $noUsers = [];
            $noUsers[] = ['_username' => "nouser", '_password' => "no user", 'permissions' => '---'];
            $this->invalid = true;
            foreach ($noUsers as $key => $value) {
                // login user
                $this->loginApi($value);
                $this->assertNull($this->getHeaders());
            }
        }

        // retornar true por defecto
        $this->assertTrue(true);
    }
}
