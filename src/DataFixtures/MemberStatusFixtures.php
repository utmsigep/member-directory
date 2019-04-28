<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\MemberStatus;

class MemberStatusFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $memberStatus = new MemberStatus();
        $memberStatus->setCode('UNDERGRADUATE');
        $memberStatus->setLabel('Undergraduate');
        $manager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('ALUMNUS');
        $memberStatus->setLabel('Alumnus');
        $manager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('RENAISSANCE');
        $memberStatus->setLabel('Renaissance (Honorary)');
        $manager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('OTHER');
        $memberStatus->setLabel('Other / Constituent');
        $manager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('RESIGNED');
        $memberStatus->setLabel('Resigned');
        $manager->persist($memberStatus);

        $memberStatus = new MemberStatus();
        $memberStatus->setCode('EXPELLED');
        $memberStatus->setLabel('Expelled');
        $manager->persist($memberStatus);

        $manager->flush();
    }
}
