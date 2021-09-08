<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200424215912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add member LinkedIn URL.';
    }

    public function up(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('linkedin_url', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('linkedin_url');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
