<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121155914 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profesional DROP referencia_laboral, DROP referencia_personal, DROP idoneidad, DROP lista_tarea, DROP archivo_dni, DROP archivo_record_policivo, DROP archivo_idoneidad, DROP servicio_departamento_id, CHANGE eliminado eliminado INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE servicio_id servicio_id INT DEFAULT NULL, CHANGE comision_id comision_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional ADD CONSTRAINT FK_2BB32E08A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profesional ADD CONSTRAINT FK_2BB32E0871CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('CREATE INDEX IDX_2BB32E08A76ED395 ON profesional (user_id)');
        $this->addSql('CREATE INDEX IDX_2BB32E0871CAA3E7 ON profesional (servicio_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profesional DROP FOREIGN KEY FK_2BB32E08A76ED395');
        $this->addSql('ALTER TABLE profesional DROP FOREIGN KEY FK_2BB32E0871CAA3E7');
        $this->addSql('DROP INDEX IDX_2BB32E08A76ED395 ON profesional');
        $this->addSql('DROP INDEX IDX_2BB32E0871CAA3E7 ON profesional');
        $this->addSql('ALTER TABLE profesional ADD referencia_laboral VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD referencia_personal VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD idoneidad VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD lista_tarea VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD archivo_dni VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD archivo_record_policivo VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD archivo_idoneidad VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD servicio_departamento_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE servicio_id servicio_id INT NOT NULL, CHANGE eliminado eliminado TINYINT(1) DEFAULT NULL, CHANGE comision_id comision_id INT NOT NULL');
    }
}
