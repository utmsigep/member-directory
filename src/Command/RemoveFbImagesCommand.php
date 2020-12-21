<?php

namespace App\Command;

use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveFbImagesCommand extends Command
{
    protected static $defaultName = 'app:remove-fb-images';
    protected $memberRepository;
    protected $em;

    public function __construct(MemberRepository $memberRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->memberRepository = $memberRepository;
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes legacy Facebook images from Member records.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->memberRepository->createQueryBuilder('m')
            ->where('m.photoUrl LIKE :facebookImage')
            ->setParameter('facebookImage', 'https://graph.facebook.com/%')
            ->getQuery()
            ->getResult();

        foreach ($result as $i => $member) {
            $io->writeln(sprintf('Updating %s ...', $member));
            $member->setPhotoUrl(null);
            $this->em->persist($member);

            if ($i%50) {
                $this->em->flush();
            }
            $this->em->flush();
        }

        $io->success(sprintf('Done! (%d records updated)', count($result)));
        return Command::SUCCESS;
    }
}
