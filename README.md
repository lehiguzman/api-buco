# Proyecto Rest API de Buco

## Descripción Breve del Proyecto

### [Proyecto desarrollado en Symfony 4](https://symfony.com/doc/4)

- [Installing & Setting up the Symfony Framework](https://symfony.com/doc/current/setup.html)
- [How to Deploy a Symfony Application](https://symfony.com/doc/current/deployment.html)
- [Symfony Flex](https://symfony.com/doc/current/setup/flex.html)
- [Creating the Project (Best Practices)](https://symfony.com/doc/current/best_practices/index.html)
- [Databases and the Doctrine ORM](https://symfony.com/doc/current/doctrine.html)
- [Checking out the Project Structure](https://symfony.com/doc/current/page_creation.html#checking-out-the-project-structure)
- [Routing](https://symfony.com/doc/current/routing.html)
- [Security](https://symfony.com/doc/current/security.html)

### Configuracin del ambiente local

Se debe crear en la computadora una llave SSH usando la siguiente documentación: [SSH Doc GitLab](https://docs.gitlab.com/ce/ssh/README.html)

## Instalación

1. Crear la carpeta en `/var/www/buco-api`.
1. Clonar el proyecto `git clone git@ssh.dev.azure.com:v3/strapptech/Buco/api`.
1. Ejecutar `composer install` o `php -d memory_limit=-1 /usr/local/bin/composer update --no-interaction`.
1. Crear virtualhost. **Opcinalmente se puede ejecutar `symfony server:start` para levantar un servidor**.

    ```xml
    <VirtualHost *:80>
        ServerName     apibuco.strappinc.net
        ServerAlias    apibuco.strappinc.net

        DocumentRoot "/var/www/buco-api/public"
        DirectoryIndex  index.php
        <Directory "/var/www/buco-api/public">
            AllowOverride None
            Allow from All
            <IfModule mod_rewrite.c>
                Options -MultiViews
                RewriteEngine On
                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteRule ^(.*)$ index.php [QSA,L]
            </IfModule>
        </Directory>

        <FilesMatch \.php$>
            SetHandler "proxy:unix:/var/run/php/php7.4-fpm.sock|fcgi://localhost/"
        </FilesMatch>

        ErrorLog ${APACHE_LOG_DIR}/buco-api-error_log
        CustomLog ${APACHE_LOG_DIR}/buco-api-access_log combined

        KeepAlive            On
        MaxKeepAliveRequests 200
        KeepAliveTimeout     5
        AddOutputFilterByType DEFLATE text/css text/plain text/html application/xhtml+xml text/xml application/xml
        <IfModule mod_headers.c>
            Header add Access-Control-Allow-Headers "ORIGIN,X-REQUESTED-WITH, CONTENT-TYPE"
            Header always set Access-Control-Allow-Methods "POST, GET, OPTIONS, PUT, DELETE, PATCH"
            Header set Access-Control-Allow-Origin "*"
            Header append Vary User-Agent env=!dont-vary
            ExpiresActive On
            ExpiresDefault "now plus 1 week"
            ExpiresByType image/x-icon "now plus 1 month"
            ExpiresByType image/gif    "now plus 1 month"
            ExpiresByType image/png    "now plus 1 month"
            ExpiresByType image/jpeg   "now plus 1 month"
        </IfModule>

        SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
        SetEnv APP_ENV dev
        SetEnv DATABASE_URL "mysql://buco_user:AbcDeFgHiJk@127.0.0.1:3306/buco_api"
    </VirtualHost>
    ```

1. En la ruta _/var/www/buco-api/var/_
    - Crear las carpetas cache, log y sessions (si no existen) con `mkdir -p var/cache var/log var/sessions`.
    - Configurar permisos a las carpetas cache, log y sessions con `sudo chmod -R 777 var/cache/ var/log/ var/sessions/`.
    - Borrar el contenido de las carpetas cache y log con `sudo rm -Rf var/cache/**/* var/log/*.log var/sessions/**/*`.
1. Ejecutar los comandos para que se pueda eliminar la cache usando el comando de symfony. [Documentación](https://symfony.com/doc/current/setup/file_permissions.html).

    ```bash
    sudo setfacl -dR -m u:www-data:rwX -m u:$(whoami):rwX var/cache var/log
    sudo setfacl -R -m u:www-data:rwX -m u:$(whoami):rwX var/cache var/log
    ```

1. Ejecutar comandos `php bin/console doctrine:database:create && php bin/console doctrine:schema:update --force`
1. Ejecutar `php bin/console doctrine:fixtures:load`. Esto carga en la base de datos el usuario **superadmin** pwd **Str4ppT3ch.,**.
1. Si todo esta bien, ejecute `apibuco.strappinc.net` o  `apibuco.strappinc.net/api/v1/doc` para acceder a la documentacion del api

---

## Configuración del JWT

[LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#generate-the-ssh-keys)

```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod -Rf 644 config/jwt/*
$
```
