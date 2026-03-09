<?php

namespace App\DataFixtures;

use App\Entity\MemberStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MemberStatusFixtures extends Fixture
{
    public const MEMBER = 'Member';
    public const ALUMNUS = 'Alumnus';
    public const EXPELLED = 'Expelled';

    public function load(ObjectManager $manager): void
    {
        $memberStatusMember = new MemberStatus();
        $memberStatusMember->setCode('MEMBER');
        $memberStatusMember->setLabel('Member');
        $manager->persist($memberStatusMember);

        $memberStatusAlumnus = new MemberStatus();
        $memberStatusAlumnus->setCode('ALUMNUS');
        $memberStatusAlumnus->setLabel('Alumnus');
        $manager->persist($memberStatusAlumnus);
        $manager->flush();

        $memberStatusExpelled = new MemberStatus();
        $memberStatusExpelled->setCode('EXPELLED');
        $memberStatusExpelled->setLabel('Expelled');
        $memberStatusExpelled->setIsInactive(true);
        $manager->persist($memberStatusExpelled);
        $manager->flush();

        $this->addReference(self::MEMBER, $memberStatusMember);
        $this->addReference(self::ALUMNUS, $memberStatusAlumnus);
        $this->addReference(self::EXPELLED, $memberStatusExpelled);
    }
}
