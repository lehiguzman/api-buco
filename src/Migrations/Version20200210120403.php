<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200210120403 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE calificacion DROP FOREIGN KEY FK_8A3AF218313D7FB9');
        $this->addSql('ALTER TABLE calificacion DROP FOREIGN KEY FK_8A3AF218A76ED395');
        $this->addSql('DROP INDEX IDX_8A3AF218313D7FB9 ON calificacion');
        $this->addSql('DROP INDEX IDX_8A3AF218A76ED395 ON calificacion');
        $this->addSql('ALTER TABLE calificacion ADD orden_servicio_id INT DEFAULT NULL, DROP user_id, DROP profesional_id');
        $this->addSql('ALTER TABLE calificacion ADD CONSTRAINT FK_8A3AF21844C5C340 FOREIGN KEY (orden_servicio_id) REFERENCES orden_servicio (id)');
        $this->addSql('CREATE INDEX IDX_8A3AF21844C5C340 ON calificacion (orden_servicio_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE calificacion DROP FOREIGN KEY FK_8A3AF21844C5C340');
        $this->addSql('DROP INDEX IDX_8A3AF21844C5C340 ON calificacion');
        $this->addSql('ALTER TABLE calificacion ADD profesional_id INT DEFAULT NULL, CHANGE orden_servicio_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE calificacion ADD CONSTRAINT FK_8A3AF218313D7FB9 FOREIGN KEY (profesional_id) REFERENCES profesional (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE calificacion ADD CONSTRAINT FK_8A3AF218A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8A3AF218313D7FB9 ON calificacion (profesional_id)');
        $this->addSql('CREATE INDEX IDX_8A3AF218A76ED395 ON calificacion (user_id)');
    }
}
