<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210133930 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orden_servicio ADD servicio_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orden_servicio ADD CONSTRAINT FK_17EC71FD71CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('CREATE INDEX IDX_17EC71FD71CAA3E7 ON orden_servicio (servicio_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orden_servicio DROP FOREIGN KEY FK_17EC71FD71CAA3E7');
        $this->addSql('DROP INDEX IDX_17EC71FD71CAA3E7 ON orden_servicio');
        $this->addSql('ALTER TABLE orden_servicio DROP servicio_id');
    }
}
