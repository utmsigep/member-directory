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
        $userTable = $schema->createTable('user');
        $userTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $userTable->addColumn('email', 'string', ['length' => 180, 'notnull' => true]);
        $userTable->addColumn('roles', 'text', ['notnull' => true, 'comment' => '(DC2Type:json)']);
        $userTable->addColumn('password', 'string', ['length' => 255, 'notnull' => true]);
        $userTable->addColumn('created_at', 'datetime', ['notnull' => true]);
        $userTable->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $userTable->addUniqueIndex(['email'], 'UNIQ_8D93D649E7927C74');
        $userTable->setPrimaryKey(['id']);

        $memberTable = $schema->createTable('member');
        $memberTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $memberTable->addColumn('status_id', 'integer', ['notnull' => false]);
        $memberTable->addColumn('local_identifier', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('external_identifier', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('first_name', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('preferred_name', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('middle_name', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('last_name', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('join_date', 'date', ['notnull' => false]);
        $memberTable->addColumn('class_year', 'integer', ['notnull' => false]);
        $memberTable->addColumn('is_deceased', 'boolean', ['notnull' => false]);
        $memberTable->addColumn('primary_email', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('primary_telephone_number', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_address_line1', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_address_line2', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_city', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_state', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_postal_code', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_country', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('mailing_latitude', 'decimal', ['precision' => 10, 'scale' => 8, 'notnull' => false]);
        $memberTable->addColumn('mailing_longitude', 'decimal', ['precision' => 11, 'scale' => 8, 'notnull' => false]);
        $memberTable->addColumn('employer', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('job_title', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('occupation', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('facebook_identifier', 'bigint', ['notnull' => false]);
        $memberTable->addColumn('is_lost', 'boolean', ['notnull' => false]);
        $memberTable->addColumn('is_local_do_not_contact', 'boolean', ['notnull' => false]);
        $memberTable->addColumn('is_external_do_not_contact', 'boolean', ['notnull' => false]);
        $memberTable->addColumn('directory_notes', 'text', ['notnull' => false]);
        $memberTable->addColumn('created_at', 'datetime');
        $memberTable->addColumn('updated_at', 'datetime');
        $memberTable->addIndex(['status_id'], 'IDX_70E4FA786BF700BD');
        $memberTable->setPrimaryKey(['id']);

        $memberStatusTable = $schema->createTable('member_status');
        $memberStatusTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $memberStatusTable->addColumn('code', 'string', ['length' => 255]);
        $memberStatusTable->addColumn('label', 'string', ['length' => 255]);
        $memberStatusTable->addColumn('created_at', 'datetime');
        $memberStatusTable->addColumn('updated_at', 'datetime');
        $memberStatusTable->setPrimaryKey(['id']);

        $extLogEntriesTable = $schema->createTable('ext_log_entries');
        $extLogEntriesTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $extLogEntriesTable->addColumn('action', 'string', ['length' => 8]);
        $extLogEntriesTable->addColumn('logged_at', 'datetime');
        $extLogEntriesTable->addColumn('object_id', 'string', ['length' => 64, 'notnull' => false]);
        $extLogEntriesTable->addColumn('object_class', 'string', ['length' => 255]);
        $extLogEntriesTable->addColumn('version', 'integer');
        $extLogEntriesTable->addColumn('data', 'text', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $extLogEntriesTable->addColumn('username', 'string', ['length' => 255, 'notnull' => false]);
        $extLogEntriesTable->addIndex(['object_class'], 'log_class_lookup_idx');
        $extLogEntriesTable->addIndex(['logged_at'], 'log_date_lookup_idx');
        $extLogEntriesTable->addIndex(['username'], 'log_user_lookup_idx');
        $extLogEntriesTable->addIndex(['object_id', 'object_class', 'version'], 'log_version_lookup_idx');
        $extLogEntriesTable->setPrimaryKey(['id']);

        $memberTable->addForeignKeyConstraint($memberStatusTable, ['status_id'], ['id'], [], 'FK_70E4FA786BF700BD');
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('member')->removeForeignKey('FK_70E4FA786BF700BD');

        $schema->dropTable('user');
        $schema->dropTable('member');
        $schema->dropTable('member_status');
        $schema->dropTable('ext_log_entries');
    }
}
