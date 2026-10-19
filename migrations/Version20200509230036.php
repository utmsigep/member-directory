<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200509230036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow other Donation fields to be null.';
    }

    public function up(Schema $schema): void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->modifyColumn('campaign', ['notnull' => false, 'default' => null]);
        $donationTable->modifyColumn('description', ['notnull' => false, 'default' => null]);
        $donationTable->modifyColumn('donation_type', ['notnull' => false, 'default' => null]);
        $donationTable->modifyColumn('card_type', ['notnull' => false, 'default' => null]);
        $donationTable->modifyColumn('last_four', ['notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema): void
    {
        $donationTable = $schema->getTable('donation');
        $donationTable->modifyColumn('campaign', ['notnull' => true, 'default' => '']);
        $donationTable->modifyColumn('description', ['notnull' => true, 'default' => '']);
        $donationTable->modifyColumn('donation_type', ['notnull' => true, 'default' => '']);
        $donationTable->modifyColumn('card_type', ['notnull' => true, 'default' => '']);
        $donationTable->modifyColumn('last_four', ['notnull' => true, 'default' => '']);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
