<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210518145002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Member birth date.';
    }

    public function up(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('birth_date', 'date', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('birth_date');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
