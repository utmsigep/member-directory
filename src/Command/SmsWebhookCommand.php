<?php

namespace App\Command;

use App\Service\SmsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:sms:webhook')]
class SmsWebhookCommand extends Command
{
    protected $smsService;

    protected $urlGenerator;

    public function __construct(SmsService $smsService, UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct();
        $this->smsService = $smsService;
        $this->urlGenerator = $urlGenerator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Get webhook URL and token for SMS integration.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->smsService->isConfigured()) {
            $io->error('SMS Service not configured.');

            return Command::FAILURE;
        }

        $io->info(sprintf(
            "Your SMS webhook token is:\n\n    %s\n\nYour webhook URL is:\n\n    %s",
            $this->smsService->getWebhookToken(),
            $this->urlGenerator->generate(
                'webhook_sms_service',
                [
                    'token' => $this->smsService->getWebhookToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ))
        );

        return Command::SUCCESS;
    }
}
