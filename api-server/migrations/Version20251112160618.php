<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112160618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE buy_price_period (
              id INT AUTO_INCREMENT NOT NULL,
              valid_from DATETIME NOT NULL,
              valid_to DATETIME DEFAULT NULL,
              price_per_kwh NUMERIC(10, 2) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE customer (
              id INT AUTO_INCREMENT NOT NULL,
              first_name VARCHAR(50) NOT NULL,
              last_name VARCHAR(50) NOT NULL,
              iban VARCHAR(50) NOT NULL,
              postcode VARCHAR(50) NOT NULL,
              house_number VARCHAR(50) NOT NULL,
              street VARCHAR(50) NOT NULL,
              city VARCHAR(50) NOT NULL,
              latitude NUMERIC(10, 4) NOT NULL,
              longitude NUMERIC(10, 4) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE device (
              id INT AUTO_INCREMENT NOT NULL,
              customer_id BIGINT NOT NULL,
              device_type VARCHAR(50) NOT NULL,
              serial_number VARCHAR(50) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE device_reading (
              id INT AUTO_INCREMENT NOT NULL,
              device_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              reading_timestamp DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              kwh_generated NUMERIC(10, 4) NOT NULL,
              price_period_id BIGINT NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE buy_price_period');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE device_reading');
    }
}
