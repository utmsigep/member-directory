<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191117223715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $memberContactRatingTable = $schema->createTable('member_contact_rating');
        $memberContactRatingTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true, 'default' => null]);
        $memberContactRatingTable->addColumn('member_id', 'integer');
        $memberContactRatingTable->addColumn('contact_rating', 'float', ['notnull' => true]);
        $memberContactRatingTable->addColumn('created_at', 'datetime', ['notnull' => true, 'default' => null]);
        $memberContactRatingTable->addColumn('updated_at', 'datetime', ['notnull' => true, 'default' => null]);
        $memberContactRatingTable->addIndex(['member_id'], 'IDX_ACF6C8107597D3FE');
        $memberContactRatingTable->setPrimaryKey(['id']);

        $memberTable = $schema->getTable('member');
        $memberContactRatingTable->addForeignKeyConstraint($memberTable, ['member_id'], ['id'], [], 'FK_ACF6C8107597D3FE');
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('member_contact_rating')->removeForeignKey('FK_ACF6C8107597D3FE');
        $schema->dropTable('member_contact_rating');
    }
}
