<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190428040306 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, status_id INT DEFAULT NULL, local_identifier VARCHAR(255) NOT NULL, external_identifier VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, preferred_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, join_date DATE NOT NULL, class_year INT NOT NULL, is_deceased TINYINT(1) NOT NULL, employer VARCHAR(255) NOT NULL, job_title VARCHAR(255) NOT NULL, occupation VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_70E4FA78150DD93A (local_identifier), UNIQUE INDEX UNIQ_70E4FA786DD00CB8 (external_identifier), INDEX IDX_70E4FA786BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_status (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, `label` VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_email (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, `label` VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, sort INT NOT NULL, INDEX IDX_85B3E9877597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_address (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, `label` VARCHAR(255) NOT NULL, address_line1 VARCHAR(255) NOT NULL, address_line2 VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, sort INT NOT NULL, INDEX IDX_B2BAD8157597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_link (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, `label` VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, sort INT NOT NULL, INDEX IDX_CA24B5AD7597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_phone_number (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, `label` VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, is_sms TINYINT(1) NOT NULL, sort INT NOT NULL, INDEX IDX_1EB222D37597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE member ADD CONSTRAINT FK_70E4FA786BF700BD FOREIGN KEY (status_id) REFERENCES member_status (id)');
        $this->addSql('ALTER TABLE member_email ADD CONSTRAINT FK_85B3E9877597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE member_address ADD CONSTRAINT FK_B2BAD8157597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE member_link ADD CONSTRAINT FK_CA24B5AD7597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
        $this->addSql('ALTER TABLE member_phone_number ADD CONSTRAINT FK_1EB222D37597D3FE FOREIGN KEY (member_id) REFERENCES member (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE member_email DROP FOREIGN KEY FK_85B3E9877597D3FE');
        $this->addSql('ALTER TABLE member_address DROP FOREIGN KEY FK_B2BAD8157597D3FE');
        $this->addSql('ALTER TABLE member_link DROP FOREIGN KEY FK_CA24B5AD7597D3FE');
        $this->addSql('ALTER TABLE member_phone_number DROP FOREIGN KEY FK_1EB222D37597D3FE');
        $this->addSql('ALTER TABLE member DROP FOREIGN KEY FK_70E4FA786BF700BD');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE member_status');
        $this->addSql('DROP TABLE member_email');
        $this->addSql('DROP TABLE member_address');
        $this->addSql('DROP TABLE member_link');
        $this->addSql('DROP TABLE member_phone_number');
    }
}
