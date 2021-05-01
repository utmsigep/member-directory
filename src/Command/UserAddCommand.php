<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;

class UserAddCommand extends Command
{
    protected static $defaultName = 'app:user:add';

    protected $entityManager;

    protected $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Adds an administrative user to the system.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password for user')
            ->addOption('roles', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'One or more roles', ['ROLE_ADMIN'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $email = $input->getArgument('email');
        $password = $input->getOption('password');
        $roles = $input->getOption('roles');

        foreach ($roles as $role) {
            $valid_roles = array_values(User::USER_ROLES);
            if (!in_array($role, $valid_roles)) {
                $io->error(sprintf("Invalid role: %s\n\nValid: %s", $role, implode(', ', $valid_roles)));
                return Command::FAILURE;
            }
        }

        $io->note(sprintf('Adding user: %s', $email));

        if (!$password) {
            $question = new Question('Enter the user\'s password:');
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $password = $helper->ask($input, $output, $question);

            $question = new Question('Confirm the user\'s password:');
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $password_confirm = $helper->ask($input, $output, $question);
            if (!$password || $password != $password_confirm) {
                $io->error('Passwords did not match, or were blank.');
                return Command::FAILURE;
            }
        }

        // Add user to the database
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $password
        ));
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $io->error('This user already exists!');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('You have added %s to the system as ID %d.', $user->getEmail(), $user->getId()));
        return Command::SUCCESS;
    }
}
