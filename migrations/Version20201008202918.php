<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201008202918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add member Facebook URL and Photo URL.';
    }

    public function up(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->addColumn('facebook_url', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
        $memberTable->addColumn('photo_url', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
    }

    public function down(Schema $schema) : void
    {
        $memberTable = $schema->getTable('member');
        $memberTable->dropColumn('facebook_url');
        $memberTable->dropColumn('photo_url');
    }

    public function postUp(Schema $schema) : void
    {
        // Forward compatability update
        $this->connection->executeQuery('UPDATE member SET facebook_url = CONCAT("https://www.facebook.com/", facebook_identifier) WHERE facebook_identifier > 0');
        $this->connection->executeQuery('UPDATE member SET photo_url = CONCAT("https://graph.facebook.com/v3.3/", facebook_identifier, "/picture?width=256&height=256&type=square") WHERE facebook_identifier > 0');
    }
}
