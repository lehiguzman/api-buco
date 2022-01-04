<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200221143126 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE profesional_metodo_pago (id INT AUTO_INCREMENT NOT NULL, profesional_id INT DEFAULT NULL, metodo_pago_id INT DEFAULT NULL, INDEX IDX_6330C4FD313D7FB9 (profesional_id), INDEX IDX_6330C4FD34676066 (metodo_pago_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE profesional_metodo_pago ADD CONSTRAINT FK_6330C4FD313D7FB9 FOREIGN KEY (profesional_id) REFERENCES profesional (id)');
        $this->addSql('ALTER TABLE profesional_metodo_pago ADD CONSTRAINT FK_6330C4FD34676066 FOREIGN KEY (metodo_pago_id) REFERENCES metodo_pago (id)');
        $this->addSql('ALTER TABLE orden_servicio ADD monto DOUBLE PRECISION DEFAULT NULL, ADD comision DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE orden_servicio_tarea ADD monto DOUBLE PRECISION DEFAULT NULL, ADD estatus INT NOT NULL');
        $this->addSql('ALTER TABLE profesional ADD anios_experiencia INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profesional ADD CONSTRAINT FK_2BB32E084B352BE1 FOREIGN KEY (comision_id) REFERENCES comision (id)');
        $this->addSql('CREATE INDEX IDX_2BB32E084B352BE1 ON profesional (comision_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE profesional_metodo_pago');
        $this->addSql('ALTER TABLE orden_servicio DROP monto, DROP comision');
        $this->addSql('ALTER TABLE orden_servicio_tarea DROP monto, DROP estatus');
        $this->addSql('ALTER TABLE profesional DROP FOREIGN KEY FK_2BB32E084B352BE1');
        $this->addSql('DROP INDEX IDX_2BB32E084B352BE1 ON profesional');
        $this->addSql('ALTER TABLE profesional DROP anios_experiencia');
    }
}
