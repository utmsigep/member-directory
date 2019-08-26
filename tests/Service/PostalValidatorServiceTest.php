<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\PostalValidatorService;
use App\Entity\Member;

class PostalValidatorServiceTest extends KernelTestCase
{
    protected $postalValidatorService;

    public function setUp()
    {
        self::bootKernel();

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        $this->postalValidatorService = $container->get(PostalValidatorService::class);

        // Skip this suite if not configured correctly
        if (!$this->postalValidatorService->isConfigured()) {
            return $this->markTestSkipped('PostalValidatorService not configured.');
        }
    }

    public function testConfigured()
    {
        $this->assertTrue($this->postalValidatorService->isConfigured());
    }

    public function testKnownAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('1100 Broadway');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37203');
        $output = $this->postalValidatorService->validate($member);

        $this->assertEquals('1100 BROADWAY', $output['AddressValidateResponse']['Address']['Address2']);
        $this->assertEquals('NASHVILLE', $output['AddressValidateResponse']['Address']['City']);
        $this->assertEquals('TN', $output['AddressValidateResponse']['Address']['State']);
        $this->assertEquals('37203', $output['AddressValidateResponse']['Address']['Zip5']);
        $this->assertEquals('3116', $output['AddressValidateResponse']['Address']['Zip4']);
    }

    public function testPOBox()
    {
        $member = new Member();
        $member->setMailingAddressLine1('PO Box 60901');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->postalValidatorService->validate($member);

        $this->assertEquals('PO BOX 60901', $output['AddressValidateResponse']['Address']['Address2']);
        $this->assertEquals('NASHVILLE', $output['AddressValidateResponse']['Address']['City']);
        $this->assertEquals('TN', $output['AddressValidateResponse']['Address']['State']);
        $this->assertEquals('37206', $output['AddressValidateResponse']['Address']['Zip5']);
        $this->assertEquals('0901', $output['AddressValidateResponse']['Address']['Zip4']);
    }

    public function testBaddAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('123 Any Street');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->postalValidatorService->validate($member);

        $this->assertEquals('-2147219401', $output['AddressValidateResponse']['Address']['Error']['Number']);
        $this->assertEquals('clsAMS', $output['AddressValidateResponse']['Address']['Error']['Source']);
        $this->assertEquals(
            'Address Not Found.',
            $output['AddressValidateResponse']['Address']['Error']['Description']
        );
        $this->assertEquals('', $output['AddressValidateResponse']['Address']['Error']['HelpFile']);
        $this->assertEquals('', $output['AddressValidateResponse']['Address']['Error']['HelpContext']);
    }
}
