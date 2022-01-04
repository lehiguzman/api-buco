<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191031192047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE metodo_pago_cliente (id INT AUTO_INCREMENT NOT NULL, cliente_id INT NOT NULL, numero_tarjeta VARCHAR(45) NOT NULL, fecha_expiracion VARCHAR(45) DEFAULT NULL, cvv VARCHAR(45) NOT NULL, nombre VARCHAR(80) NOT NULL, mes_expiracion INT DEFAULT NULL, ano_expiracion INT DEFAULT NULL, status INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE calificacion CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE departamento CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE direccion CHANGE residencia residencia VARCHAR(50) DEFAULT NULL, CHANGE piso_numero piso_numero VARCHAR(50) DEFAULT NULL, CHANGE telefono telefono VARCHAR(50) DEFAULT NULL, CHANGE instruccion instruccion VARCHAR(50) DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE documento CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE favorito CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago CHANGE status status INT DEFAULT NULL, CHANGE pago_linea pago_linea INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional CHANGE foto foto VARCHAR(45) DEFAULT NULL, CHANGE identificacion identificacion VARCHAR(45) DEFAULT NULL, CHANGE referencia_laboral referencia_laboral VARCHAR(45) DEFAULT NULL, CHANGE referencia_personal referencia_personal VARCHAR(45) DEFAULT NULL, CHANGE idoneidad idoneidad VARCHAR(45) DEFAULT NULL, CHANGE cuenta_bancaria cuenta_bancaria VARCHAR(45) DEFAULT NULL, CHANGE lista_tarea lista_tarea VARCHAR(45) DEFAULT NULL, CHANGE destreza_detalle destreza_detalle VARCHAR(45) DEFAULT NULL, CHANGE archivo_dni archivo_dni VARCHAR(45) DEFAULT NULL, CHANGE archivo_record_policivo archivo_record_policivo VARCHAR(45) DEFAULT NULL, CHANGE archivo_idoneidad archivo_idoneidad VARCHAR(45) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE latitud latitud VARCHAR(255) DEFAULT NULL, CHANGE longitud longitud VARCHAR(255) DEFAULT NULL, CHANGE radio_cobertura radio_cobertura DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_puntualidad promedio_puntualidad DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_servicio promedio_servicio DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_presencia promedio_presencia DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_conocimiento promedio_conocimiento DOUBLE PRECISION DEFAULT NULL, CHANGE promedio_recomendado promedio_recomendado DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio_departamento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE departamento_id departamento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE descripcion descripcion VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE genero genero INT DEFAULT NULL, CHANGE fechaNacimiento fechaNacimiento DATE DEFAULT NULL, CHANGE foto foto VARCHAR(255) DEFAULT NULL, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fechaEliminado fechaEliminado DATETIME DEFAULT NULL, CHANGE password_key password_key VARCHAR(255) DEFAULT NULL, CHANGE password_date password_date DATETIME DEFAULT NULL, CHANGE uuid uuid VARCHAR(255) DEFAULT NULL, CHANGE push_token push_token VARCHAR(500) DEFAULT NULL, CHANGE password_encrypted password_encrypted VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE metodo_pago_cliente');
        $this->addSql('ALTER TABLE calificacion CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE departamento CHANGE descripcion descripcion VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE direccion CHANGE residencia residencia VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE piso_numero piso_numero VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE telefono telefono VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE instruccion instruccion VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE documento CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE favorito CHANGE user_id user_id INT DEFAULT NULL, CHANGE profesional_id profesional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago CHANGE status status INT DEFAULT NULL, CHANGE pago_linea pago_linea INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional CHANGE foto foto VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE identificacion identificacion VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE referencia_laboral referencia_laboral VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE referencia_personal referencia_personal VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE idoneidad idoneidad VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE cuenta_bancaria cuenta_bancaria VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE lista_tarea lista_tarea VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE destreza_detalle destreza_detalle VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE archivo_dni archivo_dni VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE archivo_record_policivo archivo_record_policivo VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE archivo_idoneidad archivo_idoneidad VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estatus estatus INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\', CHANGE eliminado eliminado TINYINT(1) DEFAULT \'NULL\', CHANGE latitud latitud VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE longitud longitud VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE radio_cobertura radio_cobertura DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_puntualidad promedio_puntualidad DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_servicio promedio_servicio DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_presencia promedio_presencia DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_conocimiento promedio_conocimiento DOUBLE PRECISION DEFAULT \'NULL\', CHANGE promedio_recomendado promedio_recomendado DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE servicio CHANGE descripcion descripcion VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE servicio_departamento CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE departamento_id departamento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE descripcion descripcion VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fecha_eliminado fecha_eliminado DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE password_key password_key VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password_date password_date DATETIME DEFAULT \'NULL\', CHANGE genero genero INT DEFAULT NULL, CHANGE fechaNacimiento fechaNacimiento DATE DEFAULT \'NULL\', CHANGE foto foto VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estatus estatus INT DEFAULT NULL, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE fechaEliminado fechaEliminado DATETIME DEFAULT \'NULL\', CHANGE uuid uuid VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE push_token push_token VARCHAR(500) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password_encrypted password_encrypted VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
