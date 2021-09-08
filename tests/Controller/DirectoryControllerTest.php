<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
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
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/');
        $this->assertResponseRedirects('/directory/collection/member', 302);
    }

    public function testShowDirectoryCollection()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/collection/member');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.h4', 'Member');
    }

    public function testDataTablesJsonSource()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
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
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testLostMembers()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/lost');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Lost');
        $this->assertSelectorTextContains('span.h4', 'Lost');
    }

    public function testDoNotContactMembers()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/do-not-contact');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Do Not Contact');
        $this->assertSelectorTextContains('span.h4', 'Do Not Contact');
    }

    public function testDeceasedMembers()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/deceased');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Deceased');
        $this->assertSelectorTextContains('span.h4', 'Deceased');
    }

    public function testRecentChanges()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/recent-changes');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Recent Changes');
        $this->assertSelectorTextContains('span.h4', 'Recent Changes');
    }

    public function testTags()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/tags/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 1901 Club');
        $this->assertSelectorTextContains('span.h4', '1901 Club');
    }

    public function testMap()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/map');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Map');
        $this->assertSelectorTextContains('span.h4', 'Map');
    }

    public function testMapJsonSource()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/map-data');
        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonData));
        $this->assertEquals('MEMBER', $jsonData[0]->status->code);
    }
}
