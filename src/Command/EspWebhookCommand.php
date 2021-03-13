<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EspWebhookCommand extends Command
{
    protected static $defaultName = 'app:esp:webhook';
    protected static $defaultDescription = 'Manage ESP webhooks.';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('action', InputArgument::REQUIRED, 'Action to take (list, create, delete)')
            ->addOption('webhook-id', null, InputOption::VALUE_OPTIONAL, 'The UUID for the webhook.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->emailService->isConfigured()) {
            $io->error('Email Service not configured.');
            return Command::FAILURE;
        }

        switch ($input->getArgument('action')) {
            case 'list':
                $webhooks = $this->emailService->getWebhooks();
                if (!is_array($webhooks) || count($webhooks) == 0) {
                    $io->info('No webhooks configured.');
                    return Command::SUCCESS;
                }
                $table = new Table($output);
                $rows = [];
                foreach ($webhooks as $webhook) {
                    $rows[] = [
                        $webhook->WebhookID,
                        $webhook->Url,
                        implode(', ', $webhook->Events),
                        $webhook->Status,
                        mb_strtoupper($webhook->PayloadFormat)
                    ];
                }
                $table->setHeaders(['Webhook ID', 'URL', 'Events', 'Status', 'Format']);
                $table->setRows($rows);
                $table->render();
                break;
            case 'create':
                $webhook = $this->emailService->createWebhook();
                if (!$webhook) {
                    $io->error('Unable to create webhook.');
                    return Command::FAILURE;
                }
                $io->success(sprintf('Created webhook: %s', $webhook));
                break;
            case 'delete':
                $webhookId = $input->getOption('webhook-id');
                if (!$webhookId) {
                    $io->error('You must provide a --webhook-id=somestring');
                    return Command::FAILURE;
                }
                $result = $this->emailService->deleteWebhook($webhookId);
                if (!$result) {
                    $io->error(sprintf('Unable to delete webhook: %s', $webhookId));
                    return Command::FAILURE;
                }
                $io->success(sprintf('Deleted webhook: %s', $webhookId));
                break;
        }

        return Command::SUCCESS;
    }
}
