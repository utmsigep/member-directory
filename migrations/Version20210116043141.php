<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210116043141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds non-member donor fields.';
    }

    public function up(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->addColumn('donor_first_name', 'string', ['length' => 255, 'notnull' => false]);
        $donationTable->addColumn('donor_last_name', 'string', ['length' => 255, 'notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->dropColumn('donor_first_name');
        $donationTable->dropColumn('donor_last_name');
    }
}
