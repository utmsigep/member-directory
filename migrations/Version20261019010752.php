<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261019010752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop DC2Type column comments no longer used by DBAL 4.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE communication_log CHANGE logged_at logged_at DATETIME NOT NULL, CHANGE payload payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE donation CHANGE received_at received_at DATETIME NOT NULL, CHANGE transaction_payload transaction_payload JSON NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE start_at start_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ext_log_entries CHANGE data data LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL, CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE communication_log CHANGE logged_at logged_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE payload payload JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE donation CHANGE received_at received_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE transaction_payload transaction_payload JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE event CHANGE start_at start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ext_log_entries CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
