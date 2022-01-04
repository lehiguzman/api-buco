<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200205204148 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comision (id INT AUTO_INCREMENT NOT NULL, profesional_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, tipo INT NOT NULL, monto DOUBLE PRECISION DEFAULT NULL, fecha_eliminado DATETIME DEFAULT NULL, eliminado TINYINT(1) DEFAULT NULL, INDEX IDX_1013896F313D7FB9 (profesional_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comision ADD CONSTRAINT FK_1013896F313D7FB9 FOREIGN KEY (profesional_id) REFERENCES profesional (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE comision');
    }
}
