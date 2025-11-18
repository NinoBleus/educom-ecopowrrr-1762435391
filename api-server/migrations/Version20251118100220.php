<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118100220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DCB9C17E30');
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DCBA3F7A2F');
        $this->addSql('DROP INDEX IDX_4BAC25DCB9C17E30 ON device_reading');
        $this->addSql('DROP INDEX IDX_4BAC25DCBA3F7A2F ON device_reading');
        $this->addSql('ALTER TABLE device_reading ADD device_id INT NOT NULL, ADD price_period_id INT NOT NULL, DROP device_id_id, DROP price_period_id_id');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DC94A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DC6F3A4922 FOREIGN KEY (price_period_id) REFERENCES buy_price_period (id)');
        $this->addSql('CREATE INDEX IDX_4BAC25DC94A4C7D4 ON device_reading (device_id)');
        $this->addSql('CREATE INDEX IDX_4BAC25DC6F3A4922 ON device_reading (price_period_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DC94A4C7D4');
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DC6F3A4922');
        $this->addSql('DROP INDEX IDX_4BAC25DC94A4C7D4 ON device_reading');
        $this->addSql('DROP INDEX IDX_4BAC25DC6F3A4922 ON device_reading');
        $this->addSql('ALTER TABLE device_reading ADD device_id_id INT NOT NULL, ADD price_period_id_id INT NOT NULL, DROP device_id, DROP price_period_id');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DCB9C17E30 FOREIGN KEY (device_id_id) REFERENCES device (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DCBA3F7A2F FOREIGN KEY (price_period_id_id) REFERENCES buy_price_period (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4BAC25DCB9C17E30 ON device_reading (device_id_id)');
        $this->addSql('CREATE INDEX IDX_4BAC25DCBA3F7A2F ON device_reading (price_period_id_id)');
    }
}
