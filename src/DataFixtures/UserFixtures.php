<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    const ADMIN_USER = 'Admin User';

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setName('Admin User');
        $user->setEmail('admin@example.com');
        $user->setRoles([
            'ROLE_ADMIN'
        ]);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'testing'
        ));
        $user->setTimezone('America/Chicago');
        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::ADMIN_USER, $user);
    }
}
