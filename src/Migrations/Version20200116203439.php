<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200116203439 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE orden_servicio_tarea (id INT AUTO_INCREMENT NOT NULL, orden_servicio_id INT DEFAULT NULL, tarea_id INT DEFAULT NULL, INDEX IDX_5EE6373744C5C340 (orden_servicio_id), INDEX IDX_5EE637376D5BDFE1 (tarea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE orden_servicio_tarea ADD CONSTRAINT FK_5EE6373744C5C340 FOREIGN KEY (orden_servicio_id) REFERENCES orden_servicio (id)');
        $this->addSql('ALTER TABLE orden_servicio_tarea ADD CONSTRAINT FK_5EE637376D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id)');
        $this->addSql('ALTER TABLE profesional_tarea ADD precio DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE orden_servicio_tarea');
        $this->addSql('ALTER TABLE profesional_tarea DROP precio');
    }
}
