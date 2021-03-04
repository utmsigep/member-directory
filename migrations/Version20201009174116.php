<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201009174116 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add display name and last login to User table.';
    }

    public function up(Schema $schema) : void
    {
        $userTable = $schema->getTable('user');
        $userTable->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
        $userTable->addColumn('last_login', 'datetime', ['notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        $userTable = $schema->getTable('user');
        $userTable->dropColumn('name');
        $userTable->dropColumn('last_login');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
