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
        $memberStatusExpelled = $this->getReference(MemberStatusFixtures::EXPELLED);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel($memberStatusMember->getLabel());
        $directoryCollection->setIcon('fas fa-user');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusMember);
        $manager->persist($directoryCollection);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel($memberStatusAlumnus->getLabel());
        $directoryCollection->setIcon('fas fa-user-graduate');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusAlumnus);
        $manager->persist($directoryCollection);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel('Do Not Contact');
        $directoryCollection->setIcon('fas fa-ban');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusMember);
        $directoryCollection->addMemberStatus($memberStatusAlumnus);
        $directoryCollection->setFilterLocalDoNotContact('include');
        $manager->persist($directoryCollection);

        $directoryCollection = new DirectoryCollection();
        $directoryCollection->setLabel('Expelled');
        $directoryCollection->setIcon('fas fa-thumbs-down');
        $directoryCollection->setShowMemberStatus(false);
        $directoryCollection->addMemberStatus($memberStatusExpelled);
        $manager->persist($directoryCollection);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MemberStatusFixtures::class,
        ];
    }
}
