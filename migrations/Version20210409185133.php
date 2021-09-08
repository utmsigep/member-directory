<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210409185133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timezone option for User';
    }

    public function up(Schema $schema): void
    {
        $userTable = $schema->getTable('user');
        $userTable->addColumn('timezone', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema): void
    {
        $userTable = $schema->getTable('user');
        $userTable->dropColumn('timezone');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
