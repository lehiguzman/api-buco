<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203021353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE departamento DROP icono_dir, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE documento ADD fecha_eliminado DATETIME DEFAULT NULL, ADD eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago CHANGE pago_linea pago_linea TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE numero_tarjeta numero_tarjeta INT NOT NULL, CHANGE fecha_expiracion fecha_expiracion VARCHAR(7) DEFAULT NULL, CHANGE cvv cvv INT NOT NULL');
        $this->addSql('ALTER TABLE profesional CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE latitud latitud DOUBLE PRECISION DEFAULT NULL, CHANGE longitud longitud DOUBLE PRECISION DEFAULT NULL, CHANGE banco banco VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio DROP icono_dir, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE departamento ADD icono_dir VARCHAR(500) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE eliminado eliminado INT DEFAULT NULL');
        $this->addSql('ALTER TABLE documento DROP fecha_eliminado, DROP eliminado');
        $this->addSql('ALTER TABLE metodo_pago CHANGE pago_linea pago_linea INT DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE numero_tarjeta numero_tarjeta VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE fecha_expiracion fecha_expiracion VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE cvv cvv VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE profesional CHANGE banco banco VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE latitud latitud VARCHAR(40) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE longitud longitud VARCHAR(40) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE servicio ADD icono_dir VARCHAR(500) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE eliminado eliminado INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tarea CHANGE eliminado eliminado INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE eliminado eliminado INT DEFAULT NULL');
    }
}
