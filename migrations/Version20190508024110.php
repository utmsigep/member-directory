<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190508024110 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds member tagging.';
    }

    public function up(Schema $schema) : void
    {
        $tagTable = $schema->createTable('tag');
        $tagTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true, 'default' => null]);
        $tagTable->addColumn('tag_name', 'string', ['length' => 255, 'notnull' => true, 'default' => null]);
        $tagTable->addColumn('created_at', 'datetime', ['notnull' => true, 'default' => null]);
        $tagTable->addColumn('updated_at', 'datetime', ['notnull' => true, 'default' => null]);
        $tagTable->setPrimaryKey(['id']);

        $tagMemberTable = $schema->createTable('tag_member');
        $tagMemberTable->addColumn('tag_id', 'integer');
        $tagMemberTable->addColumn('member_id', 'integer');
        $tagMemberTable->addIndex(['tag_id'], 'IDX_99A5B354BAD26311');
        $tagMemberTable->addIndex(['member_id'], 'IDX_99A5B3547597D3FE');
        $tagMemberTable->setPrimaryKey(['tag_id', 'member_id']);

        $memberTable = $schema->getTable('member');

        $tagMemberTable->addForeignKeyConstraint($tagTable, ['tag_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_99A5B354BAD26311');
        $tagMemberTable->addForeignKeyConstraint($memberTable, ['member_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_99A5B3547597D3FE');
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('tag_member')->removeForeignKey('FK_99A5B354BAD26311');
        $schema->dropTable('tag');
        $schema->dropTable('tag_member');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
