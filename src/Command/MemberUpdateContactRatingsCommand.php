<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Service\ContactRatingService;
use App\Entity\Member;

class MemberUpdateContactRatingsCommand extends Command
{
    protected static $defaultName = 'app:member:update-contact-rating';

    protected $entityManager;

    protected $contact;

    public function __construct(EntityManagerInterface $entityManager, ContactRatingService $contactRatingService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->contactRatingService = $contactRatingService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update Member Contact Rating')
            ->addArgument('localIdentifier', InputArgument::OPTIONAL, 'Local Identifier for member.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Whether to loop through all records.')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Save the result to the Member Record.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $localIdentifier = $input->getArgument('localIdentifier');

        if ($input->getOption('all')) {
            $members = $this->entityManager->getRepository(Member::class)->findByStatusCodes([
                'ALUMNUS',
                'UNDERGRADUATE',
                'RENAISSANCE'
            ]);
        } else {
            $members = $this->entityManager->getRepository(Member::class)->findBy([
                'localIdentifier' => $localIdentifier
            ]);
            if (empty($members)) {
                $io->error('Member not found matching Local Identifier: ' . $localIdentifier);
                return 1;
            }
        }

        foreach ($members as $member) {
            try {
                $io->title($member->getDisplayName());
                $member = $this->contactRatingService->scoreMember($member);
                $io->writeln('<options=bold>Contact Rating:</>  ' . $member->getContactRating());
            } catch (\Exception $e) {
                $io->error($e->getMessage());
                return 1;
            }

            if ($input->getOption('save')) {
                $this->entityManager->persist($member);
                $this->entityManager->flush();
            }
        }

        $io->success('Done!');
    }
}
