<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210305034503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure unique local and external identifier.';
    }

    public function up(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addUniqueIndex(['local_identifier'], 'UNIQ_70E4FA78150DD93A');
        $memberTable->addUniqueIndex(['external_identifier'], 'UNIQ_70E4FA786DD00CB8');
    }

    public function down(Schema $schema): void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropIndex('UNIQ_70E4FA78150DD93A');
        $memberTable->dropIndex('UNIQ_70E4FA786DD00CB8');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
