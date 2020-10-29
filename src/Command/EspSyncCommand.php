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
    protected $entityManager;

    protected $emailService;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $members = $this->entityManager->getRepository(Member::class)->findByActiveMemberStatuses();
        // Only work with records that have an email set
        $members = array_filter($members, function ($record) {
            return (bool) $record->getPrimaryEmail();
        });
        $recordCount = count($members);

        // Set up progress bar
        $progressBar = new ProgressBar($output, $recordCount);
        $progressBar->start();

        // Main loop
        $output = [
            'subscribed' => [],
            'unsubscribed' => [],
            'updated' => [],
            'ignored' => []
        ];
        foreach ($members as $member) {
            $subscription = $this->emailService->getMemberSubscription($member);
            // If not found, attempt to subscribe user
            if (!$subscription || empty($subscription) || property_exists($subscription, 'Code')) {
                if (!$member->getIsLocalDoNotContact()) {
                    $output['subscribed'][] = $member->getDisplayName();
                    $this->emailService->subscribeMember($member);
                } else {
                    $output['ignored'][] = $member->getDisplayName();
                }
            // If is found, check subscription status and update if Active
            } else {
                if ($subscription->State == 'Active') {
                    if (!$member->getIsLocalDoNotContact()) {
                        $output['updated'][] = $member->getDisplayName();
                        $this->emailService->updateMember($member->getPrimaryEmail(), $member);
                    } else {
                        $output['unsubscribed'][] = $member->getDisplayName();
                        $this->emailService->unsubscribeMember($member);
                    }
                } else {
                    $output['ignored'][] = $member->getDisplayName();
                }
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        $io->title('Subscribed');
        $io->writeln(implode(PHP_EOL, $output['subscribed']));
        $io->title('Unsubscribed');
        $io->writeln(implode(PHP_EOL, $output['unsubscribed']));
        $io->title('Updated');
        $io->writeln(implode(PHP_EOL, $output['updated']));
        $io->title('Ignored (Unsubscribed/Do Not Contact)');
        $io->writeln(implode(PHP_EOL, $output['ignored']));

        $io->success('Done!');
        return 0;
    }
}
