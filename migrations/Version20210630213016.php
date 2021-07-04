<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210630213016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add event attendence record.';
    }

    public function up(Schema $schema): void
    {
        $eventTable = $schema->createTable('event');
        $eventTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true, 'default' => null]);
        $eventTable->addColumn('name', 'string', ['length' => 255]);
        $eventTable->addColumn('code', 'string', ['length' => 255]);
        $eventTable->addColumn('location', 'string', ['length' => 255]);
        $eventTable->addColumn('description', 'text');
        $eventTable->addColumn('start_at', 'datetime', ['comment' => '(DC2Type:datetime_immutable)']);
        $eventTable->addColumn('created_at', 'datetime');
        $eventTable->addColumn('updated_at', 'datetime');
        $eventTable->setPrimaryKey(['id']);

        $eventMemberTable = $schema->createTable('event_member');
        $eventMemberTable->addColumn('event_id', 'integer');
        $eventMemberTable->addColumn('member_id', 'integer');
        $eventMemberTable->addIndex(['member_id'], 'IDX_427D8D2A7597D3FE');
        $eventMemberTable->addIndex(['event_id'], 'IDX_427D8D2A71F7E88B');
        $eventMemberTable->setPrimaryKey(['event_id', 'member_id']);

        $memberTable = $schema->getTable('member');

        $eventMemberTable->addForeignKeyConstraint($eventTable, ['event_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_427D8D2A71F7E88B');
        $eventMemberTable->addForeignKeyConstraint($memberTable, ['member_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_427D8D2A7597D3FE');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('event_member')->removeForeignKey('FK_427D8D2A71F7E88B');
        $schema->dropTable('event');
        $schema->dropTable('event_member');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
