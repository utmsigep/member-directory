<?php

namespace App\DataFixtures;

use App\Entity\CommunicationLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommunicationLogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $uncleBilly = $this->getReference(MemberFixtures::UNCLE_BILLY);
        $user = $this->getReference(UserFixtures::USER_COMMUNICATIONS_MANAGER);

        $communicationLog = new CommunicationLog();
        $communicationLog->setLoggedAt(new \DateTimeImmutable('2016-11-01 14:00 CDT'));
        $communicationLog->setMember($uncleBilly);
        $communicationLog->setSummary('Sent a text inviting Uncle Billy to Conclave.');
        $communicationLog->setType('SMS');
        $communicationLog->setUser($user);
        $manager->persist($communicationLog);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            MemberFixtures::class
        ];
    }
}
