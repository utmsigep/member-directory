<?php

namespace App\Command;

use App\Entity\Member;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:esp:sync')]
class EspSyncCommand extends Command
{
    protected $entityManager;

    protected $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the Email Service Provider with directory data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->emailService->isConfigured()) {
            $io->error('Email service not configured.');

            return Command::FAILURE;
        }
        $members = $this->entityManager->getRepository(Member::class)->findActiveEmailable();
        $recordCount = count($members);

        // Set up progress bar
        $progressBar = new ProgressBar($output, $recordCount);
        $progressBar->start();

        // Main loop
        $output = [
            'subscribed' => [],
            'unsubscribed' => [],
            'updated' => [],
            'ignored' => [],
        ];
        foreach ($members as $member) {
            $subscription = $this->emailService->getMemberSubscription($member);
            // If not found, attempt to subscribe user
            if (!$subscription || property_exists($subscription, 'Code')) {
                if (!$member->getIsLocalDoNotContact()) {
                    $this->emailService->subscribeMember($member);
                    $output['subscribed'][] = $member->getDisplayName();
                } else {
                    $output['ignored'][] = $member->getDisplayName();
                }
            // If is found, check subscription status and update if Active
            } else {
                if ('Active' == $subscription->State) {
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

        return Command::SUCCESS;
    }
}
