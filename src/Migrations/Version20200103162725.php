<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200103162725 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE servicio_tipo_documento (id INT AUTO_INCREMENT NOT NULL, servicio_id INT DEFAULT NULL, tipo_documento_id INT DEFAULT NULL, INDEX IDX_CEA7915071CAA3E7 (servicio_id), INDEX IDX_CEA79150F6939175 (tipo_documento_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE servicio_tipo_documento ADD CONSTRAINT FK_CEA7915071CAA3E7 FOREIGN KEY (servicio_id) REFERENCES servicio (id)');
        $this->addSql('ALTER TABLE servicio_tipo_documento ADD CONSTRAINT FK_CEA79150F6939175 FOREIGN KEY (tipo_documento_id) REFERENCES tipo_documento (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE servicio_tipo_documento');
    }
}
