<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201023030317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Updates for Gedmo Loggable';
    }

    public function up(Schema $schema) : void
    {
        $extLogEntriesTable = $schema->getTable('ext_log_entries');
        $extLogEntriesTable->changeColumn('object_class', ['length' => 191, 'nullable' => false]);
        $extLogEntriesTable->changeColumn('username', ['length' => 191, 'nullable' => true]);
    }

    public function down(Schema $schema) : void
    {
        $extLogEntriesTable = $schema->getTable('ext_log_entries');
        $extLogEntriesTable->changeColumn('object_class', ['length' => 255, 'nullable' => false]);
        $extLogEntriesTable->changeColumn('username', ['length' => 255, 'nullable' => true]);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
