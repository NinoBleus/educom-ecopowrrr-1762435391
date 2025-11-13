<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113102115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device ADD customer_id_id INT NOT NULL, DROP customer_id, CHANGE id id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_92FB68EB171EB6C ON device (customer_id_id)');
        $this->addSql('ALTER TABLE device_reading ADD device_id_id VARCHAR(255) NOT NULL, ADD price_period_id_id INT NOT NULL, DROP device_id, DROP price_period_id');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DCB9C17E30 FOREIGN KEY (device_id_id) REFERENCES device (id)');
        $this->addSql('ALTER TABLE device_reading ADD CONSTRAINT FK_4BAC25DCBA3F7A2F FOREIGN KEY (price_period_id_id) REFERENCES buy_price_period (id)');
        $this->addSql('CREATE INDEX IDX_4BAC25DCB9C17E30 ON device_reading (device_id_id)');
        $this->addSql('CREATE INDEX IDX_4BAC25DCBA3F7A2F ON device_reading (price_period_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EB171EB6C');
        $this->addSql('DROP INDEX IDX_92FB68EB171EB6C ON device');
        $this->addSql('ALTER TABLE device ADD customer_id BIGINT NOT NULL, DROP customer_id_id, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DCB9C17E30');
        $this->addSql('ALTER TABLE device_reading DROP FOREIGN KEY FK_4BAC25DCBA3F7A2F');
        $this->addSql('DROP INDEX IDX_4BAC25DCB9C17E30 ON device_reading');
        $this->addSql('DROP INDEX IDX_4BAC25DCBA3F7A2F ON device_reading');
        $this->addSql('ALTER TABLE device_reading ADD device_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD price_period_id BIGINT NOT NULL, DROP device_id_id, DROP price_period_id_id');
    }
}
