<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200909030146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Directory Collections, add inactive flag to Member Status.';
    }

    public function up(Schema $schema) : void
    {
        $directoryCollectionTable = $schema->createTable('directory_collection');
        $directoryCollectionTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $directoryCollectionTable->addColumn('label', 'string', ['length' => 255, 'notnull' => true]);
        $directoryCollectionTable->addColumn('icon', 'string', ['length' => 255, 'notnull' => true]);
        $directoryCollectionTable->addColumn('show_member_status', 'boolean', ['notnull' => true]);
        $directoryCollectionTable->addColumn('group_by', 'string', ['length' => 255, 'notnull' => false]);
        $directoryCollectionTable->addColumn('slug', 'string', ['length' => 255, 'notnull' => false]);
        $directoryCollectionTable->addColumn('position', 'integer', ['notnull' => false]);
        $directoryCollectionTable->addColumn('filter_lost', 'string', ['length' => 255, 'notnull' => false]);
        $directoryCollectionTable->addColumn('filter_local_do_not_contact', 'string', ['length' => 255, 'notnull' => false]);
        $directoryCollectionTable->addColumn('filter_deceased', 'string', ['length' => 255, 'notnull' => false]);
        $directoryCollectionTable->setPrimaryKey(['id']);

        $directoryCollectionMemberStatusTable = $schema->createTable('directory_collection_member_status');
        $directoryCollectionMemberStatusTable->addColumn('directory_collection_id', 'integer');
        $directoryCollectionMemberStatusTable->addColumn('member_status_id', 'integer');
        $directoryCollectionMemberStatusTable->addIndex(['directory_collection_id'], 'IDX_CC64EF66E9D937AC');
        $directoryCollectionMemberStatusTable->addIndex(['member_status_id'], 'IDX_CC64EF662BDFD678');
        $directoryCollectionMemberStatusTable->setPrimaryKey(['directory_collection_id', 'member_status_id']);

        $memberStatusTable = $schema->getTable('member_status');
        $memberStatusTable->addColumn('is_inactive', 'boolean', ['notnull' => true]);

        $directoryCollectionMemberStatusTable->addForeignKeyConstraint($directoryCollectionTable, ['directory_collection_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_CC64EF66E9D937AC');
        $directoryCollectionMemberStatusTable->addForeignKeyConstraint($memberStatusTable, ['member_status_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_CC64EF662BDFD678');

        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('is_external_do_not_contact');
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('directory_collection_member_status')->removeForeignKey('FK_CC64EF66E9D937AC');
        $schema->dropTable('directory_collection');
        $schema->dropTable('directory_collection_member_status');
        $schema->getTable('member')->addColumn('is_external_do_not_contact', 'boolean', ['notnull' => false]);
        $schema->getTable('member_status')->dropColumn('is_inactive');
    }

    public function postUp(Schema $schema) : void
    {
        // Forward compatability update
        $this->connection->executeQuery('UPDATE member_status SET is_inactive = 1 WHERE code IN ("RESIGNED", "EXPELLED")');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
