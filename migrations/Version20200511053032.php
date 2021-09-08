<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200511053032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fulltext index on Member table.';
    }

    public function up(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addIndex(['first_name', 'preferred_name', 'middle_name', 'last_name'], 'idx_70e4fa78a9d1c13261cd21aa59107af8c808ba5a');
        $index = $memberTable->getIndex('idx_70e4fa78a9d1c13261cd21aa59107af8c808ba5a');
        $index->addFlag('fulltext');
    }

    public function down(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropIndex('idx_70e4fa78a9d1c13261cd21aa59107af8c808ba5a');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
