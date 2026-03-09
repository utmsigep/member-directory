<?php

namespace App\DataFixtures;

use App\Entity\CommunicationLog;
use App\Entity\Member;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommunicationLogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $uncleBilly = $this->getReference(MemberFixtures::UNCLE_BILLY, Member::class);
        $user = $this->getReference(UserFixtures::USER_COMMUNICATIONS_MANAGER, User::class);

        $communicationLog = new CommunicationLog();
        $communicationLog->setLoggedAt(new \DateTimeImmutable('2016-11-01 14:00 CDT'));
        $communicationLog->setMember($uncleBilly);
        $communicationLog->setSummary('Sent a text inviting Uncle Billy to Conclave.');
        $communicationLog->setType('SMS');
        $communicationLog->setUser($user);
        $manager->persist($communicationLog);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MemberFixtures::class,
        ];
    }
}
