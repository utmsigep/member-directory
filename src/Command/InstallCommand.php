<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\MemberStatus;

class InstallCommand extends Command
{
    protected static $defaultName = 'app:install';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Installs necessary records into the database for the application to run.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Sanity check to prevent the installer from running twice
        if (!empty($this->entityManager->getRepository(MemberStatus::class)->findAll())) {
            $io->error('MemberStatus records already exist!');
            return false;
        }

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('UNDERGRADUATE');
        $memberStatus->setLabel('Undergraduate');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('ALUMNUS');
        $memberStatus->setLabel('Alumnus');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('RENAISSANCE');
        $memberStatus->setLabel('Renaissance (Honorary)');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('OTHER');
        $memberStatus->setLabel('Other / Constituent');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('RESIGNED');
        $memberStatus->setLabel('Resigned');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('EXPELLED');
        $memberStatus->setLabel('Expelled');
        $this->entityManager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('TRANSFERRED');
        $memberStatus->setLabel('Transferred');
        $this->entityManager->persist($memberStatus);

        $this->entityManager->flush();

        $io->success('Necessary records installed! Run `app:user:add` to add your first user.');
    }
}
