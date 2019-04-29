<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190429003353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, status_id INT DEFAULT NULL, local_identifier VARCHAR(255) NOT NULL, external_identifier VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, preferred_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, join_date DATE NOT NULL, class_year INT DEFAULT NULL, is_deceased TINYINT(1) NOT NULL, employer VARCHAR(255) DEFAULT NULL, primary_email VARCHAR(255) DEFAULT NULL, primary_telephone_number VARCHAR(255) DEFAULT NULL, mailing_address_line1 VARCHAR(255) DEFAULT NULL, mailing_address_line2 VARCHAR(255) DEFAULT NULL, mailing_city VARCHAR(255) DEFAULT NULL, mailing_state VARCHAR(255) DEFAULT NULL, mailing_postal_code VARCHAR(255) DEFAULT NULL, mailing_country VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, occupation VARCHAR(255) DEFAULT NULL, is_local_do_not_contact TINYINT(1) NOT NULL, is_external_do_not_contact TINYINT(1) NOT NULL, directory_notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_70E4FA78150DD93A (local_identifier), UNIQUE INDEX UNIQ_70E4FA786DD00CB8 (external_identifier), INDEX IDX_70E4FA786BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_status (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, `label` VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE member ADD CONSTRAINT FK_70E4FA786BF700BD FOREIGN KEY (status_id) REFERENCES member_status (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member DROP FOREIGN KEY FK_70E4FA786BF700BD');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE member_status');
        $this->addSql('DROP TABLE ext_log_entries');
    }
}
