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
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER);

        $communicationLog = new CommunicationLog();
        $communicationLog->setLoggedAt(new \DateTime('November 1, 2016'));
        $communicationLog->setMember($uncleBilly);
        $communicationLog->setSummary('Sent a text inviting Uncle Billy to Conclave.');
        $communicationLog->setType('SMS');
        $communicationLog->setUser($adminUser);
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
