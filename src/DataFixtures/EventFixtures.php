<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $uncleBilly = $this->getReference(MemberFixtures::UNCLE_BILLY, Member::class);

        $event = new Event();
        $event->setStartAt(new \DateTimeImmutable('2021-08-05 19:00 CDT'));
        $event->setName('Alumni Meetup');
        $event->setLocation('Panucci\'s Pizza');
        $event->setDescription('Summer meetup.');
        $event->addAttendee($uncleBilly);
        $manager->persist($event);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MemberFixtures::class,
        ];
    }
}
