<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200503030035 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Allow nulls in donorComment and internalNotes.';
    }

    public function up(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('donor_comment', ['notnull' => false, 'default' => null]);
        $donationTable->changeColumn('internal_notes', ['notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('donor_comment', ['notnull' => true, 'default' => '']);
        $donationTable->changeColumn('internal_notes', ['notnull' => true, 'default' => '']);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
