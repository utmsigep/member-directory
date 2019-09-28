<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190907030410 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds contact rating to Member.';
    }

    public function up(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('contact_rating', 'float', ['notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('contact_rating');
    }
}
