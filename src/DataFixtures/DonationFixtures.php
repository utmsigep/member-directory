<?php

namespace App\DataFixtures;

use App\Entity\Donation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DonationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $uncleBilly = $this->getReference(MemberFixtures::UNCLE_BILLY);

        $donation = new Donation();
        $donation->setReceivedAt(new \DateTime('November 1, 2016'));
        $donation->setMember($uncleBilly);
        $donation->setReceiptIdentifier('11011901-001');
        $donation->setCampaign('Zollinger House');
        $donation->setDescription('Capital Campaign Contribution');
        $donation->setAmount('1000');
        $donation->setCurrency('USD');
        $donation->setProcessingFee('30');
        $donation->setNetAmount('970');
        $donation->setIsAnonymous(false);
        $donation->setIsRecurring(true);
        $manager->persist($donation);

        $donation = new Donation();
        $donation->setReceivedAt(new \DateTime('November 1, 2017'));
        $donation->setMember($uncleBilly);
        $donation->setReceiptIdentifier('11011901-002');
        $donation->setCampaign('Zollinger House');
        $donation->setDescription('Capital Campaign Contribution');
        $donation->setAmount('1000');
        $donation->setCurrency('USD');
        $donation->setProcessingFee('30');
        $donation->setNetAmount('970');
        $donation->setIsAnonymous(false);
        $donation->setIsRecurring(false);
        $manager->persist($donation);

        $donation = new Donation();
        $donation->setReceivedAt(new \DateTime('November 1, 2018'));
        $donation->setMember($uncleBilly);
        $donation->setReceiptIdentifier('11011901-003');
        $donation->setCampaign('Zollinger House');
        $donation->setDescription('Capital Campaign Contribution');
        $donation->setAmount('1000');
        $donation->setCurrency('USD');
        $donation->setProcessingFee('30');
        $donation->setNetAmount('970');
        $donation->setIsAnonymous(false);
        $donation->setIsRecurring(true);
        $manager->persist($donation);

        $donation = new Donation();
        $donation->setReceivedAt(new \DateTime('November 1, 2019'));
        $donation->setMember($uncleBilly);
        $donation->setReceiptIdentifier('11011901-004');
        $donation->setCampaign('Zollinger House');
        $donation->setDescription('Capital Campaign Contribution');
        $donation->setAmount('1000');
        $donation->setCurrency('USD');
        $donation->setProcessingFee('30');
        $donation->setNetAmount('970');
        $donation->setIsAnonymous(false);
        $donation->setIsRecurring(true);
        $manager->persist($donation);

        $donation = new Donation();
        $donation->setReceivedAt(new \DateTime('November 1, 2019'));
        $donation->setMember($uncleBilly);
        $donation->setReceiptIdentifier('11011901-004');
        $donation->setCampaign('NUTS! Scholarship');
        $donation->setDescription('');
        $donation->setAmount('1000');
        $donation->setCurrency('USD');
        $donation->setProcessingFee('30');
        $donation->setNetAmount('970');
        $donation->setIsAnonymous(true);
        $donation->setIsRecurring(true);
        $manager->persist($donation);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MemberFixtures::class
        ];
    }
}
