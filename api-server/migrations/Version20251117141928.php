<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117141928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EB171EB6C');
        $this->addSql('DROP INDEX IDX_92FB68EB171EB6C ON device');
        $this->addSql('ALTER TABLE device CHANGE customer_id_id customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_92FB68E9395C3F3 ON device (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68E9395C3F3');
        $this->addSql('DROP INDEX IDX_92FB68E9395C3F3 ON device');
        $this->addSql('ALTER TABLE device CHANGE customer_id customer_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_92FB68EB171EB6C ON device (customer_id_id)');
    }
}
