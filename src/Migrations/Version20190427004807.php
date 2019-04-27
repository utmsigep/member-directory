<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190427004807 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add user table';
    }

    public function up(Schema $schema) : void
    {
        $userTable = $schema->createTable('user');
        $userTable->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $userTable->addColumn('email', 'string', ['length' => 180, 'notnull' => true]);
        $userTable->addColumn('roles', 'text', ['notnull' => true]);
        $userTable->addColumn('password', 'string', ['length' => 255, 'notnull' => true]);
        $userTable->addUniqueIndex(['email'], 'UNIQ_8D93D649E7927C74');
        $userTable->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('user');
    }
}
