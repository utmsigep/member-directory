<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Service\GeocoderService;
use App\Entity\Member;

class MemberGeocodeCommand extends Command
{
    protected static $defaultName = 'app:member:geocode';

    protected $entityManager;

    protected $validator;

    protected $geocoderService;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, GeocoderService $geocoderService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->geocoderService = $geocoderService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Geocode a member\'s address')
            ->addArgument('localIdentifier', InputArgument::REQUIRED, 'Local Identifier for member.')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Save the result to the Member record.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $localIdentifier = $input->getArgument('localIdentifier');

        $member = $this->entityManager->getRepository(Member::class)->findOneBy([
            'localIdentifier' => $localIdentifier
        ]);
        if (is_null($member)) {
            $io->error('Member not found matching Local Identifier: ' . $localIdentifier);
            return 1;
        }

        // Clear existing coordinates
        $member->setMailingLatitude(null);
        $member->setMailingLongitude(null);

        try {
            $io->title($member->getDisplayName());
            $io->writeln('<options=bold>Address Line 1:</>  ' . $member->getMailingAddressLine1());
            $io->writeln('<options=bold>Address Line 2:</>  ' . $member->getMailingAddressLine2());
            $io->writeln('<options=bold>City:</>            ' . $member->getMailingCity());
            $io->writeln('<options=bold>State:</>           ' . $member->getMailingState());
            $io->writeln('<options=bold>Postal Code:</>     ' . $member->getMailingPostalCode());
            $io->writeln('');
            $this->geocoderService->geocodeMemberMailingAddress($member);
            $io->writeln('<options=bold>Latitude:</>        ' . $member->getMailingLatitude());
            $io->writeln('<options=bold>Longitude:</>       ' . $member->getMailingLongitude());
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return 1;
        }

        if ($input->getOption('save')) {
            $this->entityManager->persist($member);
            $this->entityManager->flush();
        }

        $io->success('Done!');
    }
}
