<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Member;
use App\Service\EmailService;

class EspAuditCommand extends Command
{
    protected static $defaultName = 'app:esp:audit';

    protected $entityManager;

    protected $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Walks through active user emails and finds those with no activity.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->emailService->isConfigured()) {
            $io->error('Email service not configured.');
            return 1;
        }

        $members = $this->entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE',
            'UNDERGRADUATE',
            'TRANSFERRED',
            'OTHER'
        ]);

        foreach ($members as $member) {
            // Exlcude a few cases from report
            if ($member->getIsDeceased()
                || $member->getIsLocalDoNotContact()
                || $member->getIsLost()
            ) {
                continue;
            }

            if (!$member->getPrimaryEmail()) {
                // $io->writeln(sprintf(
                //     '[%s] No email set for %s',
                //     $member->getDisplayName()
                // ));
                continue;
            }

            $history = $this->emailService->getMemberSubscriptionHistory($member);
            if (!$history) {
                $io->writeln(sprintf(
                    '[%s] No email history for %s (%s)',
                    $member->getLocalIdentifier(),
                    $member->getDisplayName(),
                    $member->getPrimaryEmail()
                ));
                continue;
            }

            foreach ($history as $campaign) {
                if (!empty($campaign->Actions)) {
                    // We've got some action, so this one doesn't need to be in the report.
                    continue 2;
                }
            }

            $io->writeln(sprintf(
                '[%s] No recent activity for %s (%s)',
                $member->getLocalIdentifier(),
                $member->getDisplayName(),
                $member->getPrimaryEmail()
            ));
        }

        $io->success('Done!');

        return 0;
    }
}
