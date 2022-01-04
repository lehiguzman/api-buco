<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216142546 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE metodo_pago ADD eliminado TINYINT(1) DEFAULT NULL, ADD fecha_eliminado DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE numero_tarjeta numero_tarjeta VARCHAR(255) NOT NULL, CHANGE fecha_expiracion fecha_expiracion VARCHAR(255) DEFAULT NULL, CHANGE mes_expiracion mes_expiracion VARCHAR(255) DEFAULT NULL, CHANGE ano_expiracion ano_expiracion VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE metodo_pago DROP eliminado, DROP fecha_eliminado');
        $this->addSql('ALTER TABLE metodo_pago_cliente CHANGE numero_tarjeta numero_tarjeta VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE fecha_expiracion fecha_expiracion DATETIME DEFAULT NULL, CHANGE mes_expiracion mes_expiracion INT DEFAULT NULL, CHANGE ano_expiracion ano_expiracion INT DEFAULT NULL');
    }
}
