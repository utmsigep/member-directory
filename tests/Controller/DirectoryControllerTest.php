<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Service\PostalValidatorService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DirectoryControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/directory/collection/member');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testRedirectToFirstCollection()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/');
        $this->assertResponseRedirects('/directory/collection/member', 302);
    }

    public function testShowDirectoryCollection()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/collection/member');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.display-6', 'Member');
    }

    public function testDataTablesJsonSource()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/collection/member.json');
        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(11, $jsonData->recordsTotal);
        $this->assertEquals('MEMBER', $jsonData->data[0]->status->code);
    }

    public function testShowMember()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkens');
        $this->assertSelectorTextContains('span.display-6', 'Carter Jenkens');
    }

    public function testLostMembers()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/lost');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Lost');
        $this->assertSelectorTextContains('span.display-6', 'Lost');
    }

    public function testDoNotContactMembers()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/do-not-contact');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Do Not Contact');
        $this->assertSelectorTextContains('span.display-6', 'Do Not Contact');
    }

    public function testDeceasedMembers()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/deceased');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Deceased');
        $this->assertSelectorTextContains('span.display-6', 'Deceased');
    }

    public function testRecentChanges()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/recent-changes');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Recent Changes');
        $this->assertSelectorTextContains('span.display-6', 'Recent Changes');
    }

    public function testTags()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/tags/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 1901 Club');
        $this->assertSelectorTextContains('span.display-6', '1901 Club');
    }

    public function testMap()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/map');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Map');
        $this->assertSelectorTextContains('span.display-6', 'Map');
    }

    public function testMapJsonSource()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/map-data');
        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonData));
        $this->assertEquals('MEMBER', $jsonData[0]->status->code);
    }

    public function testVerifyAddressDataSuccess()
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $postalValidatorService = $this->createMock(PostalValidatorService::class);
        $postalValidatorService->method('isConfigured')->willReturn(true);
        $postalValidatorService->method('validate')->willReturn([
            'address' => [
                'streetAddress' => '1100 BROADWAY',
                'secondaryAddress' => 'APT 1',
                'city' => 'NASHVILLE',
                'state' => 'TN',
                'ZIPCode' => '37203',
                'ZIPPlus4' => '3116',
            ],
        ]);
        $container->set(PostalValidatorService::class, $postalValidatorService);

        $client->request('GET', '/directory/verify-address-data', [
            'mailingAddressLine1' => '1100 Broadway',
            'mailingAddressLine2' => 'Apt 1',
            'mailingCity' => 'Nashville',
            'mailingState' => 'TN',
            'mailingPostalCode' => '37203',
            'cacheBust' => uniqid('verify-', true),
        ]);

        $this->assertResponseIsSuccessful();
        $jsonData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('success', $jsonData['status']);
        $this->assertSame('1100 BROADWAY', $jsonData['address']['streetAddress']);
        $this->assertSame('37203', $jsonData['address']['ZIPCode']);
        $this->assertSame('3116', $jsonData['address']['ZIPPlus4']);
    }

    public function testVerifyAddressDataError()
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $postalValidatorService = $this->createMock(PostalValidatorService::class);
        $postalValidatorService->method('isConfigured')->willReturn(true);
        $postalValidatorService->method('validate')->willReturn([
            'error' => [
                'code' => '404',
                'message' => 'There is no match for the address requested.',
            ],
        ]);
        $container->set(PostalValidatorService::class, $postalValidatorService);

        $client->request('GET', '/directory/verify-address-data', [
            'mailingAddressLine1' => '123 Any Street',
            'mailingCity' => 'Nashville',
            'mailingState' => 'TN',
            'mailingPostalCode' => '37206',
            'cacheBust' => uniqid('verify-', true),
        ]);

        $this->assertResponseStatusCodeSame(500);
        $jsonData = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertSame('error', $jsonData['status']);
        $this->assertSame('There is no match for the address requested.', $jsonData['message']);
    }
}
