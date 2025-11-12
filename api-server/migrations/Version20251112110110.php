<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112110110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, postcode VARCHAR(10) NOT NULL, house_number VARCHAR(10) NOT NULL, street VARCHAR(50) NOT NULL, city VARCHAR(50) NOT NULL, municipality_id BIGINT NOT NULL, latitude NUMERIC(10, 4) NOT NULL, longitude NUMERIC(10, 4) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE buy_price_period (id INT AUTO_INCREMENT NOT NULL, valid_from DATETIME NOT NULL, valid_to DATETIME DEFAULT NULL, buy_price_per_kwh NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_address (id INT AUTO_INCREMENT NOT NULL, customer_id BIGINT NOT NULL, address_id BIGINT NOT NULL, is_primary TINYINT(1) NOT NULL, valid_from DATE NOT NULL, valid_to DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(50) NOT NULL, phone VARCHAR(50) NOT NULL, iban VARCHAR(50) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, device_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', customer_id BIGINT NOT NULL, device_name VARCHAR(50) NOT NULL, serial_nummer VARCHAR(50) NOT NULL, device_type_id BIGINT NOT NULL, status VARCHAR(255) NOT NULL, installed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', decommissioned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_message (id INT AUTO_INCREMENT NOT NULL, message_id VARCHAR(50) NOT NULL, device_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', device_status VARCHAR(255) NOT NULL, message_datetime DATETIME NOT NULL, raw_json JSON NOT NULL COMMENT \'(DC2Type:json)\', processed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processing_status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_reading (id INT AUTO_INCREMENT NOT NULL, batch_id BIGINT NOT NULL, device_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', reading_timestamp DATETIME NOT NULL, device_total_yield_kwh NUMERIC(10, 4) NOT NULL, device_month_yield_kwh NUMERIC(10, 4) NOT NULL, device_status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_type (id INT AUTO_INCREMENT NOT NULL, device_type_id BIGINT NOT NULL, name VARCHAR(50) NOT NULL, desccription LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE energy_purchase (id INT AUTO_INCREMENT NOT NULL, customer_id BIGINT NOT NULL, device_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', reading_id BIGINT NOT NULL, price_period_id BIGINT NOT NULL, kwh NUMERIC(10, 4) NOT NULL, amount_eur NUMERIC(10, 2) NOT NULL, purchase_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE municipality (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reading_batch (id INT AUTO_INCREMENT NOT NULL, collected_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', total_usage_kwh NUMERIC(10, 4) NOT NULL, sourche_message_id VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE buy_price_period');
        $this->addSql('DROP TABLE customer_address');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE device_message');
        $this->addSql('DROP TABLE device_reading');
        $this->addSql('DROP TABLE device_type');
        $this->addSql('DROP TABLE energy_purchase');
        $this->addSql('DROP TABLE municipality');
        $this->addSql('DROP TABLE reading_batch');
    }
}
