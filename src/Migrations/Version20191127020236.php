<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127020236 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profesional CHANGE direccion direccion VARCHAR(255) NOT NULL, CHANGE destreza_detalle destreza_detalle VARCHAR(500) DEFAULT NULL, CHANGE latitud latitud VARCHAR(40) DEFAULT NULL, CHANGE longitud longitud VARCHAR(40) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profesional CHANGE direccion direccion VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE destreza_detalle destreza_detalle VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE latitud latitud VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE longitud longitud VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
