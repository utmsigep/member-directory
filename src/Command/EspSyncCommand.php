<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

use App\Entity\Member;
use App\Service\EmailService;

class EspSyncCommand extends Command
{
    protected static $defaultName = 'app:esp:sync';

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates the Email Service Provider with directory data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $members = $this->entityManager->getRepository(Member::class)->findByStatusCodes([
            'ALUMNUS',
            'RENAISSANCE',
            'UNDERGRDAUTE',
            'OTHER'
        ]);
        // Only work with records that have an email set
        $members = array_filter($members, function ($record) {
            return (bool) $record->getPrimaryEmail();
        });
        $recordCount = count($members);

        // Set up progress bar
        $progressBar = new ProgressBar($output, $recordCount);
        $progressBar->start();

        // Main loop
        foreach ($members as $member) {
            $subscription = $this->emailService->getMemberSubscription($member);
            // If not found, attempt to subscribe user
            if (!$subscription || empty($subscription) || property_exists($subscription, 'Code')) {
                $io->writeln('Subscribing ' . $member->getDisplayName());
                $this->emailService->subscribeMember($member);
            // If is found, check subscription status and update if Active
            } else {
                if ($subscription->State == 'Active') {
                    $io->writeln('Updating ' . $member->getDisplayName());
                    $this->emailService->updateMember($member->getPrimaryEmail(), $member);
                } else {
                    $io->writeln('Ignoring unsubscribed ' . $member->getDisplayName());
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        $io->success('Done!');
    }
}
