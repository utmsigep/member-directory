<?php

namespace App\Tests\Service;

use App\Entity\Member;
use App\Service\PostalValidatorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PostalValidatorServiceTest extends TestCase
{
    public function testConfigured(): void
    {
        $service = new PostalValidatorService(
            new ParameterBag(['usps.auth_token' => 'test-token']),
            new MockHttpClient()
        );

        $this->assertTrue($service->isConfigured());
    }

    public function testKnownAddress(): void
    {
        $service = $this->buildService([
            new MockResponse(json_encode([
                'address' => [
                    'streetAddress' => '1100 BROADWAY',
                    'city' => 'NASHVILLE',
                    'state' => 'TN',
                    'ZIPCode' => '37203',
                    'ZIPPlus4' => '3116',
                ],
            ]) ?: '{}', [
                'http_code' => 200,
            ]),
        ]);

        $member = new Member();
        $member->setMailingAddressLine1('1100 Broadway');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37203');
        $output = $service->validate($member);

        $this->assertArrayHasKey('address', $output);
        $this->assertEquals('1100 BROADWAY', $output['address']['streetAddress']);
        $this->assertEquals('NASHVILLE', $output['address']['city']);
        $this->assertEquals('TN', $output['address']['state']);
        $this->assertEquals('37203', $output['address']['ZIPCode']);
        $this->assertEquals('3116', $output['address']['ZIPPlus4']);
    }

    public function testPOBox(): void
    {
        $service = $this->buildService([
            new MockResponse(json_encode([
                'address' => [
                    'streetAddress' => 'PO BOX 60901',
                    'city' => 'NASHVILLE',
                    'state' => 'TN',
                    'ZIPCode' => '37206',
                    'ZIPPlus4' => '0901',
                ],
            ]) ?: '{}', [
                'http_code' => 200,
            ]),
        ]);

        $member = new Member();
        $member->setMailingAddressLine1('PO Box 60901');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $service->validate($member);

        $this->assertArrayHasKey('address', $output);
        $this->assertEquals('PO BOX 60901', $output['address']['streetAddress']);
        $this->assertEquals('NASHVILLE', $output['address']['city']);
        $this->assertEquals('TN', $output['address']['state']);
        $this->assertEquals('37206', $output['address']['ZIPCode']);
        $this->assertEquals('0901', $output['address']['ZIPPlus4']);
    }

    public function testBadAddress(): void
    {
        $service = $this->buildService([
            new MockResponse(json_encode([
                'error' => [
                    'code' => '404',
                    'message' => 'There is no match for the address requested.',
                ],
            ]) ?: '{}', [
                'http_code' => 404,
            ]),
        ]);

        $member = new Member();
        $member->setMailingAddressLine1('123 Any Street');
        $member->setMailingCity('Nashville');
        $member->setMailingState('TN');
        $member->setMailingPostalCode('37206');
        $output = $service->validate($member);

        $this->assertArrayHasKey('error', $output);
        $this->assertArrayHasKey('message', $output['error']);
        $this->assertSame('There is no match for the address requested.', $output['error']['message']);
    }

    public function testMissingRequiredFieldsReturnsErrorWithoutHttpCall(): void
    {
        $service = $this->buildService([]);

        $member = new Member();
        $member->setMailingAddressLine1('');
        $member->setMailingCity('');
        $member->setMailingState('');

        $output = $service->validate($member);

        $this->assertSame('400', $output['error']['code']);
        $this->assertSame('Street address, state, and either city or ZIP Code are required.', $output['error']['message']);
    }

    private function buildService(array $responses): PostalValidatorService
    {
        return new PostalValidatorService(
            new ParameterBag(['usps.auth_token' => 'test-token']),
            new MockHttpClient($responses)
        );
    }
}
