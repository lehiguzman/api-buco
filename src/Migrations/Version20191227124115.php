<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191227124115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE profesional_tarea (id INT AUTO_INCREMENT NOT NULL, profesional_id INT DEFAULT NULL, tarea_id INT DEFAULT NULL, INDEX IDX_1FE5E487313D7FB9 (profesional_id), INDEX IDX_1FE5E4876D5BDFE1 (tarea_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE profesional_tarea ADD CONSTRAINT FK_1FE5E487313D7FB9 FOREIGN KEY (profesional_id) REFERENCES profesional (id)');
        $this->addSql('ALTER TABLE profesional_tarea ADD CONSTRAINT FK_1FE5E4876D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE profesional_tarea');
    }
}
