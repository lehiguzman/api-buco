<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200110154946 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE orden_servicio (id INT AUTO_INCREMENT NOT NULL, servicio INT NOT NULL, profesional INT NOT NULL, fecha VARCHAR(255) NOT NULL, hora VARCHAR(255) NOT NULL, metodo_pago INT NOT NULL, latitud DOUBLE PRECISION NOT NULL, longitud DOUBLE PRECISION NOT NULL, direccion VARCHAR(255) DEFAULT NULL, estatus INT DEFAULT NULL, descripcion VARCHAR(255) DEFAULT NULL, observacion VARCHAR(255) DEFAULT NULL, fecha_registrado DATETIME NOT NULL, eliminado TINYINT(1) DEFAULT NULL, fecha_eliminado DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calificacion CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE departamento CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE icono icono VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE direccion CHANGE residencia residencia VARCHAR(50) DEFAULT NULL, CHANGE piso_numero piso_numero VARCHAR(50) DEFAULT NULL, CHANGE telefono telefono VARCHAR(50) DEFAULT NULL, CHANGE instruccion instruccion VARCHAR(50) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE documento CHANGE profesional_id profesional_id INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE favorito CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE firebase CHANGE id_token id_token VARCHAR(1000) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago CHANGE status status INT DEFAULT NULL, CHANGE pago_linea pago_linea TINYINT(1) DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE fecha_expiracion fecha_expiracion VARCHAR(255) DEFAULT NULL, CHANGE mes_expiracion mes_expiracion VARCHAR(255) DEFAULT NULL, CHANGE ano_expiracion ano_expiracion VARCHAR(255) DEFAULT NULL, CHANGE status status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE identificacion identificacion VARCHAR(45) DEFAULT NULL, CHANGE cuenta_bancaria cuenta_bancaria VARCHAR(45) DEFAULT NULL, CHANGE destreza_detalle destreza_detalle VARCHAR(500) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE comision_id comision_id INT DEFAULT NULL, CHANGE latitud latitud DOUBLE PRECISION DEFAULT NULL, CHANGE longitud longitud DOUBLE PRECISION DEFAULT NULL, CHANGE radio_cobertura radio_cobertura DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_puntualidad promedio_puntualidad DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_servicio promedio_servicio DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_presencia promedio_presencia DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_conocimiento promedio_conocimiento DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_recomendado promedio_recomendado DOUBLE PRECISION DEFAULT NULL, CHANGE tipo_cuenta tipo_cuenta INT DEFAULT NULL, CHANGE banco banco VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional_tarea CHANGE profesional_id profesional_id INT DEFAULT NULL, CHANGE tarea_id tarea_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE icono icono VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio_departamento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE departamento_id departamento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio_tipo_documento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE tipo_documento_id tipo_documento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tipo_documento CHANGE periodicidad periodicidad INT DEFAULT NULL, CHANGE requiere_verificacion requiere_verificacion TINYINT(1) DEFAULT NULL, CHANGE requiere_copia requiere_copia TINYINT(1) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE genero genero INT DEFAULT NULL, CHANGE foto foto VARCHAR(255) DEFAULT NULL, CHANGE password_key password_key VARCHAR(255) DEFAULT NULL, CHANGE password_date password_date DATETIME DEFAULT NULL, CHANGE uuid uuid VARCHAR(255) DEFAULT NULL, CHANGE push_token push_token VARCHAR(500) DEFAULT NULL, CHANGE password_encrypted password_encrypted VARCHAR(255) DEFAULT NULL, CHANGE id_token id_token VARCHAR(1000) DEFAULT NULL, CHANGE fecha_nacimiento fecha_nacimiento DATE DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_2DA17977E7927C74');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649f85e0677 TO UNIQ_2DA17977F85E0677');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE orden_servicio');
        $this->addSql('ALTER TABLE calificacion CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE departamento CHANGE descripcion descripcion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE icono icono VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE direccion CHANGE residencia residencia VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE piso_numero piso_numero VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE telefono telefono VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE instruccion instruccion VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE documento CHANGE profesional_id profesional_id INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE favorito CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Firebase CHANGE id_token id_token VARCHAR(1000) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE metodo_pago CHANGE status status INT DEFAULT NULL, CHANGE pago_linea pago_linea TINYINT(1) DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE fecha_expiracion fecha_expiracion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE mes_expiracion mes_expiracion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE ano_expiracion ano_expiracion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE status status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional CHANGE user_id user_id INT DEFAULT NULL, CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE identificacion identificacion VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE tipo_cuenta tipo_cuenta INT DEFAULT NULL, CHANGE banco banco VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE cuenta_bancaria cuenta_bancaria VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE destreza_detalle destreza_detalle VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE estatus estatus INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE comision_id comision_id INT DEFAULT NULL, CHANGE latitud latitud DOUBLE PRECISION DEFAULT \'NULL\', CHANGE longitud longitud DOUBLE PRECISION DEFAULT \'NULL\', CHANGE radio_cobertura radio_cobertura DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_puntualidad promedio_puntualidad DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_servicio promedio_servicio DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_presencia promedio_presencia DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_conocimiento promedio_conocimiento DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_recomendado promedio_recomendado DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE profesional_tarea CHANGE profesional_id profesional_id INT DEFAULT NULL, CHANGE tarea_id tarea_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio CHANGE descripcion descripcion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE icono icono VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE servicio_departamento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE departamento_id departamento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio_tipo_documento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE tipo_documento_id tipo_documento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE descripcion descripcion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tipo_documento CHANGE periodicidad periodicidad INT DEFAULT NULL, CHANGE requiere_verificacion requiere_verificacion TINYINT(1) DEFAULT \'NULL\', CHANGE requiere_copia requiere_copia TINYINT(1) DEFAULT \'NULL\', CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE User CHANGE password_key password_key VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE password_date password_date DATETIME DEFAULT \'NULL\', CHANGE genero genero INT DEFAULT NULL, CHANGE fecha_nacimiento fecha_nacimiento DATE DEFAULT \'NULL\', CHANGE foto foto VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE uuid uuid VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE push_token push_token VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE id_token id_token VARCHAR(1000) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE password_encrypted password_encrypted VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE User RENAME INDEX uniq_2da17977e7927c74 TO UNIQ_8D93D649E7927C74');
        $this->addSql('ALTER TABLE User RENAME INDEX uniq_2da17977f85e0677 TO UNIQ_8D93D649F85E0677');
    }
}
