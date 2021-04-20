<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210419212458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add payload field to CommunicationLog';
    }

    public function up(Schema $schema) : void
    {
        $communicationLogTable = $schema->getTable('communication_log');
        $communicationLogTable->addColumn('payload', 'text', ['notnull' => false, 'default' => null, 'comment' => '(DC2Type:json)']);
    }

    public function down(Schema $schema) : void
    {
        $communicationLogTable = $schema->getTable('communication_log');
        $communicationLogTable->dropColumn('payload');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
