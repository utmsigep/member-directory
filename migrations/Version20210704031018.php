<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210704031018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Donations and Communication Log use DateTimeImmutable';
    }

    public function up(Schema $schema): void
    {
        $communicationLogTable = $schema->getTable('communication_log');
        $communicationLogTable->changeColumn('logged_at', ['comment' => '(DC2Type:datetime_immutable)']);
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('received_at', ['comment' => '(DC2Type:datetime_immutable)']);
    }

    public function down(Schema $schema): void
    {
        $communicationLogTable = $schema->getTable('communication_log');
        $communicationLogTable->changeColumn('logged_at', ['comment' => null]);
        $donationTable = $schema->getTable('donation');
        $donationTable->changeColumn('received_at', ['comment' => null]);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
