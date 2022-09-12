<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessengerControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithDCommunicationManagerRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/messenger/email', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/messenger/email', 302);
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testEmail()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/email');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Messenger - Email');
        $this->assertSelectorTextContains('span.display-6', 'Send Bulk Email');
    }

    public function testSms()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/sms');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Messenger - SMS');
        $this->assertSelectorTextContains('span.display-6', 'Send Bulk SMS');
    }
}
