<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201009035253 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Drop Facebook Identifier column.';
    }

    public function up(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('facebook_identifier');
    }

    public function down(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('facebook_identifier', 'bigint', ['notnull' => false]);
    }
}
