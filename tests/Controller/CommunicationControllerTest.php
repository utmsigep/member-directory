<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommunicationControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/communications/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithDCommunicationManagerRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Communications');
        $this->assertSelectorTextContains('span.display-6', 'Communications');
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Communications');
        $this->assertSelectorTextContains('span.display-6', 'Communications');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowCommunication()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Communications Log #1 for Phillips, Billy (1-0006)');
        $this->assertSelectorTextContains('span.display-6', 'Communications Log #1 for Phillips, Billy (1-0006)');
    }

    public function testEditCommunication()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Communications Log #1 for Phillips, Billy (1-0006)');
        $this->assertSelectorTextContains('span.display-6', 'Communications Log #1 for Phillips, Billy (1-0006)');
    }

    public function testNewCommunication()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/communications/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Communication');
        $this->assertSelectorTextContains('span.display-6', 'New Communication');
    }
}
