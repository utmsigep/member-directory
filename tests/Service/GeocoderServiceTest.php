<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\GeocoderService;
use App\Entity\Member;

class GeocoderServiceTest extends KernelTestCase
{
    protected $geocoderService;

    public function setUp()
    {
        self::bootKernel();

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        $this->geocoderService = $container->get(GeocoderService::class);
    }

    public function testKnownAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('1100 Broadway');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        $this->assertEquals('36.15711', $output->getMailingLatitude());
        $this->assertEquals('-86.78627', $output->getMailingLongitude());
    }

    public function testPOBox()
    {
        $member = new Member();
        $member->setMailingAddressLine1('PO Box 60901');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        // Reverts to 100 Main St
        $this->assertEquals(36.182265, $output->getMailingLatitude());
        $this->assertEquals(-86.731055, $output->getMailingLongitude());
    }

    public function testBadAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('123 Any Street');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        // Reverts to 100 Main St
        $this->assertEquals(36.182265, $output->getMailingLatitude());
        $this->assertEquals(-86.731055, $output->getMailingLongitude());
    }

}
