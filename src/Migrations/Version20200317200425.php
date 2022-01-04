<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200317200425 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documento ADD tipo_documento_id INT DEFAULT NULL, ADD copia VARCHAR(255) DEFAULT NULL, ADD fecha_vencimiento DATE DEFAULT NULL, ADD vencido TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE documento ADD CONSTRAINT FK_B6B12EC7F6939175 FOREIGN KEY (tipo_documento_id) REFERENCES tipo_documento (id)');
        $this->addSql('CREATE INDEX IDX_B6B12EC7F6939175 ON documento (tipo_documento_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE documento DROP FOREIGN KEY FK_B6B12EC7F6939175');
        $this->addSql('DROP INDEX IDX_B6B12EC7F6939175 ON documento');
        $this->addSql('ALTER TABLE documento DROP tipo_documento_id, DROP copia, DROP fecha_vencimiento, DROP vencido');
    }
}
