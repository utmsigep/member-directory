<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220112032431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name prefix and suffix fields';
    }

    public function up(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('prefix', 'string', ['length' => 255, 'notnull' => false]);
        $memberTable->addColumn('suffix', 'string', ['length' => 255, 'notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('prefix');
        $memberTable->dropColumn('suffix');
    }
}
