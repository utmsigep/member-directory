<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210409154321 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Communication';
    }

    public function up(Schema $schema) : void
    {
        $communicationLogTable = $schema->createTable('communication_log');
        $communicationLogTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $communicationLogTable->addColumn('member_id', 'integer', ['notnull' => true]);
        $communicationLogTable->addColumn('logged_at', 'datetime', ['notnull' => true, 'default' => null]);
        $communicationLogTable->addColumn('type', 'string', ['length' => 255, 'notnull' => true]);
        $communicationLogTable->addColumn('summary', 'text', ['notnull' => true]);
        $communicationLogTable->addColumn('user_id', 'integer', ['notnull' => false, 'default' => null]);
        $communicationLogTable->addColumn('created_at', 'datetime', ['notnull' => true, 'default' => null]);
        $communicationLogTable->addColumn('updated_at', 'datetime', ['notnull' => true, 'default' => null]);
        $communicationLogTable->setPrimaryKey(['id']);
        $communicationLogTable->addIndex(['member_id'], 'IDX_ED4161637597D3FE');
        $communicationLogTable->addIndex(['user_id'], 'IDX_ED416163A76ED395');

        $memberTable = $schema->getTable('member');
        $communicationLogTable->addForeignKeyConstraint($memberTable, ['member_id'], ['id'], [], 'FK_ED4161637597D3FE');
        $userTable = $schema->getTable('user');
        $communicationLogTable->addForeignKeyConstraint($userTable, ['user_id'], ['id'], [], 'FK_ED416163A76ED395');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('communication_log');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
