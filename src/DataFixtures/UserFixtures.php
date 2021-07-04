<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    const USER_BASIC = 'Basic User';
    const USER_DIRECTORY_MANAGER = 'Directory Manager';
    const USER_COMMUNICATIONS_MANAGER = 'Communications Manager';
    const USER_DONATION_MANAGER = 'Donation Manager';
    const USER_EVENT_MANAGER = 'Event Manager';
    const USER_EMAIL_MANAGER = 'Email Manager';
    const USER_ADMIN = 'Administrator';

    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Basic User');
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_BASIC, $user);

        $user = new User();
        $user->setName('Directory Manager');
        $user->setEmail('directory.manager@example.com');
        $user->setRoles(['ROLE_DIRECTORY_MANAGER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_DIRECTORY_MANAGER, $user);

        $user = new User();
        $user->setName('Communications Manager');
        $user->setEmail('communications.manager@example.com');
        $user->setRoles(['ROLE_COMMUNICATIONS_MANAGER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_COMMUNICATIONS_MANAGER, $user);

        $user = new User();
        $user->setName('Donation Manager');
        $user->setEmail('donation.manager@example.com');
        $user->setRoles(['ROLE_DONATION_MANAGER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_DONATION_MANAGER, $user);

        $user = new User();
        $user->setName('Email Manager');
        $user->setEmail('email.manager@example.com');
        $user->setRoles(['ROLE_EMAIL_MANAGER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_EMAIL_MANAGER, $user);

        $user = new User();
        $user->setName('Event Manager');
        $user->setEmail('event.manager@example.com');
        $user->setRoles(['ROLE_EVENT_MANAGER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_EVENT_MANAGER, $user);

        $user = new User();
        $user->setName('Admin User');
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'testing'));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_ADMIN, $user);
    }
}
