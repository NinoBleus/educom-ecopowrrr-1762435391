<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121121847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD municipality VARCHAR(255) NOT NULL, ADD province VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE device_reading CHANGE kwh_generated kwh_generated NUMERIC(10, 4) NOT NULL, CHANGE kwh_used kwh_used NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP municipality, DROP province');
        $this->addSql('ALTER TABLE device_reading CHANGE kwh_generated kwh_generated NUMERIC(10, 4) DEFAULT NULL, CHANGE kwh_used kwh_used NUMERIC(10, 2) NOT NULL');
    }
}
