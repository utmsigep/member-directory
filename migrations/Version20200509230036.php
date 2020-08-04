<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200509230036 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Allow other Donation fields to be null.';
    }

    public function up(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('campaign', ['notnull' => false, 'default' => null]);
        $donationTable->changeColumn('description', ['notnull' => false, 'default' => null]);
        $donationTable->changeColumn('donation_type', ['notnull' => false, 'default' => null]);
        $donationTable->changeColumn('card_type', ['notnull' => false, 'default' => null]);
        $donationTable->changeColumn('last_four', ['notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema) : void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('campaign', ['notnull' => true, 'default' => '']);
        $donationTable->changeColumn('description', ['notnull' => true, 'default' => '']);
        $donationTable->changeColumn('donation_type', ['notnull' => true, 'default' => '']);
        $donationTable->changeColumn('card_type', ['notnull' => true, 'default' => '']);
        $donationTable->changeColumn('last_four', ['notnull' => true, 'default' => '']);
    }
}
