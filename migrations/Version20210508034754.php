<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210508034754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add PasswordResetRequest table.';
    }

    public function up(Schema $schema): void
    {
        $resetPasswordRequestTable = $schema->createTable('reset_password_request');
        $resetPasswordRequestTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $resetPasswordRequestTable->addColumn('user_id', 'integer', ['notnull' => true]);
        $resetPasswordRequestTable->addColumn('selector', 'string', ['length' => 20, 'notnull' => true]);
        $resetPasswordRequestTable->addColumn('hashed_token', 'string', ['length' => 100, 'notnull' => true]);
        $resetPasswordRequestTable->addColumn('requested_at', 'datetime', ['notnull' => true, 'comment' => '(DC2Type:datetime_immutable)']);
        $resetPasswordRequestTable->addColumn('expires_at', 'datetime', ['notnull' => true, 'comment' => '(DC2Type:datetime_immutable)']);
        $resetPasswordRequestTable->addIndex(['user_id'], 'IDX_7CE748AA76ED395');
        $resetPasswordRequestTable->setPrimaryKey(['id']);

        $userTable = $schema->getTable('user');
        $resetPasswordRequestTable->addForeignKeyConstraint($userTable, ['user_id'], ['id'], [], 'FK_7CE748AA76ED395');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('reset_password_request');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
