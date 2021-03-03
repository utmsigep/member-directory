<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201001033047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add TOTP secret to user table.';
    }

    public function up(Schema $schema) : void
    {
        $userTable = $schema->getTable('user');
        $userTable->addColumn('totp_secret', 'string', ['length' => 255, 'notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        $userTable = $schema->getTable('user');
        $userTable->dropColumn('totp_secret');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
