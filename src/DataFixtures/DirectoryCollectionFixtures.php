<?php

namespace App\DataFixtures;

use App\Entity\DirectoryCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DirectoryCollectionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $memberStatusMember = $this->getReference(MemberStatusFixtures::MEMBER);
        $memberStatusAlumnus = $this->getReference(MemberStatusFixtures::ALUMNUS);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel($memberStatusMember->getLabel());
        $directoryCollection->setIcon('fa-user');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusMember);
        $manager->persist($directoryCollection);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel($memberStatusAlumnus->getLabel());
        $directoryCollection->setIcon('fa-user-graduate');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusAlumnus);
        $manager->persist($directoryCollection);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel('Do Not Contact');
        $directoryCollection->setIcon('fa-ban');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusMember);
        $directoryCollection->addMemberStatus($memberStatusAlumnus);
        $directoryCollection->setFilterLocalDoNotContact('include');
        $manager->persist($directoryCollection);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MemberStatusFixtures::class
        ];
    }
}
