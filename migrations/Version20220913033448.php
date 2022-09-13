<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220913033448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a description to DirectoryCollection';
    }

    public function up(Schema $schema): void
    {
        $directoryCollectionTable = $schema->getTable('directory_collection');
        $directoryCollectionTable->addColumn('description', 'text', ['notnull' => false]);
    }

    public function down(Schema $schema): void
    {
        $directoryCollectionTable = $schema->getTable('directory_collection');
        $directoryCollectionTable->dropColumn('description');
    }
}
