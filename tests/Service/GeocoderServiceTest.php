<?php

namespace App\Tests\Service;

use App\Entity\Member;
use App\Service\GeocoderService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GeocoderServiceTest extends KernelTestCase
{
    protected GeocoderService $geocoderService;

    public function setUp(): void
    {
        self::bootKernel();

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        $mockHttpClient = new MockHttpClient(function ($method, $url) {
            switch ($url) {
                case 'https://geocoding.geo.census.gov/geocoder/locations/address?street=1100%20Broadway%20&city=Nashville&state=TN&benchmark=Public_AR_Current&format=json':
                    return new MockResponse(file_get_contents(dirname(__FILE__).'/fixtures/census_api_known_address.json'));
                case 'https://geocoding.geo.census.gov/geocoder/locations/address?street=PO%20Box%2060901%20&city=Nashville&state=TN&zip=37206&benchmark=Public_AR_Current&format=json':
                    return new MockResponse(file_get_contents(dirname(__FILE__).'/fixtures/census_api_po_box.json'));
                case 'https://geocoding.geo.census.gov/geocoder/locations/address?street=123%20Any%20Street%20&city=Nashville&state=TN&zip=37206&benchmark=Public_AR_Current&format=json':
                    return new MockResponse(file_get_contents(dirname(__FILE__).'/fixtures/census_api_bad_street_address.json'));
                case 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find/?text=37206&maxLocations=1&f=json&returnGeometry=true':
                    return new MockResponse(file_get_contents(dirname(__FILE__).'/fixtures/arcgis_zip_code.json'));
                default:
                    throw new \Exception('Did not match known request for '.$method.': '.$url);
            }
        });

        $this->geocoderService = $container->get(GeocoderService::class);
        $this->geocoderService->setHttpClient($mockHttpClient);
    }

    public function testKnownAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('1100 Broadway');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        $this->assertGreaterThan(36.1, $output->getMailingLatitude());
        $this->assertLessThan(-86.7, $output->getMailingLongitude());
    }

    public function testPOBox()
    {
        $member = new Member();
        $member->setMailingAddressLine1('PO Box 60901');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        // Falls back to Zip Code location
        $this->assertGreaterThan(36.1, $output->getMailingLatitude());
        $this->assertLessThan(-86.7, $output->getMailingLongitude());
    }

    public function testBadAddress()
    {
        $member = new Member();
        $member->setMailingAddressLine1('123 Any Street');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $this->geocoderService->geocodeMemberMailingAddress($member);

        // Falls back to Zip Code location
        $this->assertGreaterThan(36.1, $output->getMailingLatitude());
        $this->assertLessThan(-86.7, $output->getMailingLongitude());
    }
}
