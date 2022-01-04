<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200206152323 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE comision DROP FOREIGN KEY FK_1013896F313D7FB9');
        $this->addSql('DROP INDEX IDX_1013896F313D7FB9 ON comision');
        $this->addSql('ALTER TABLE comision ADD porcentaje DOUBLE PRECISION DEFAULT NULL, DROP profesional_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE comision ADD profesional_id INT DEFAULT NULL, DROP porcentaje');
        $this->addSql('ALTER TABLE comision ADD CONSTRAINT FK_1013896F313D7FB9 FOREIGN KEY (profesional_id) REFERENCES profesional (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1013896F313D7FB9 ON comision (profesional_id)');
    }
}
