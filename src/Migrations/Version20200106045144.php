<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106045144 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds member donations.';
    }

    public function up(Schema $schema) : void
    {
        $donationTable = $schema->createTable('donation');
        $donationTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true, 'default' => null]);
        $donationTable->addColumn('member_id', 'integer', ['notnull' => false, 'default' => null]);
        $donationTable->addColumn('receipt_identifier', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
        $donationTable->addColumn('received_at', 'datetime', ['notnull' => true, 'default' => null]);
        $donationTable->addColumn('campaign', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('description', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => true, 'default' => null]);
        $donationTable->addColumn('currency', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('processing_fee', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => true, 'default' => null]);
        $donationTable->addColumn('net_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => true, 'default' => null]);
        $donationTable->addColumn('donor_comment', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('internal_notes', 'text', ['notnull' => true]);
        $donationTable->addColumn('donation_type', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('card_type', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('last_four', 'string', ['length' => 255, 'notnull' => true]);
        $donationTable->addColumn('is_anonymous', 'boolean', ['notnull' => true]);
        $donationTable->addColumn('is_recurring', 'boolean', ['notnull' => true]);
        $donationTable->addColumn('transaction_payload', 'json', ['comment' => '(DC2Type:json)']);
        $donationTable->addColumn('created_at', 'datetime', ['notnull' => true, 'default' => null]);
        $donationTable->addColumn('updated_at', 'datetime', ['notnull' => true, 'default' => null]);
        $donationTable->addIndex(['member_id'], 'IDX_31E581A07597D3FE');
        $donationTable->setPrimaryKey(['id']);
        $memberTable = $schema->getTable('member');
        $donationTable->addForeignKeyConstraint($memberTable, ['member_id'], ['id'], [], 'FK_31E581A07597D3FE');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('donation');
    }
}
