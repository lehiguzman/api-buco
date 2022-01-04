<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191023173911 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE servicio_departamento (id INT AUTO_INCREMENT NOT NULL, servicio_id INT DEFAULT NULL, departamento_id INT DEFAULT NULL, INDEX IDX_2423A29071CAA3E7 (servicio_id), INDEX IDX_2423A2905A91C08D (departamento_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE servicio_departamento ADD CONSTRAINT FK_2423A29071CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE servicio_departamento ADD CONSTRAINT FK_2423A2905A91C08D FOREIGN KEY (departamento_id) REFERENCES departamento (id)');
        $this->addSql('ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22A5A91C08D');
        $this->addSql('DROP INDEX IDX_CB86F22A5A91C08D ON servicio');
        $this->addSql('ALTER TABLE servicio DROP departamento_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE servicio_departamento');
        $this->addSql('ALTER TABLE servicio ADD departamento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22A5A91C08D FOREIGN KEY (departamento_id) REFERENCES departamento (id)');
        $this->addSql('CREATE INDEX IDX_CB86F22A5A91C08D ON servicio (departamento_id)');
    }
}
