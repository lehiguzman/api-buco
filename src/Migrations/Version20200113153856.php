<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200113153856 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE metodo_pago_cliente ADD user_id INT DEFAULT NULL, ADD mes_anio_expiracion VARCHAR(5) DEFAULT NULL, ADD token VARCHAR(255) DEFAULT NULL, ADD fecha_eliminado DATETIME DEFAULT NULL, ADD eliminado TINYINT(1) DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP cliente_id, DROP fecha_expiracion, DROP mes_expiracion, DROP ano_expiracion, CHANGE numero_tarjeta numero_tarjeta VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE metodo_pago_cliente ADD CONSTRAINT FK_F9FFAC80A76ED395 FOREIGN KEY (user_id) REFERENCES User (id)');
        $this->addSql('CREATE INDEX IDX_F9FFAC80A76ED395 ON metodo_pago_cliente (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE metodo_pago_cliente DROP FOREIGN KEY FK_F9FFAC80A76ED395');
        $this->addSql('DROP INDEX IDX_F9FFAC80A76ED395 ON metodo_pago_cliente');
        $this->addSql('ALTER TABLE metodo_pago_cliente ADD cliente_id INT NOT NULL, ADD mes_expiracion VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD ano_expiracion VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP user_id, DROP mes_anio_expiracion, DROP fecha_eliminado, DROP eliminado, DROP created_at, DROP updated_at, CHANGE numero_tarjeta numero_tarjeta VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE token fecha_expiracion VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
