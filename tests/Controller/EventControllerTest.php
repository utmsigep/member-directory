<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/events/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithEventManagerRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('event.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Events');
        $this->assertSelectorTextContains('span.h4', 'Events');
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Events');
        $this->assertSelectorTextContains('span.h4', 'Events');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowEvents()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('event.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 8/5/2021: Alumni Meetup');
        $this->assertSelectorTextContains('span.h4', '8/5/2021: Alumni Meetup');
    }

    public function testEditEvent()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('event.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 8/5/2021: Alumni Meetup');
        $this->assertSelectorTextContains('span.h4', '8/5/2021: Alumni Meetup');
    }

    public function testNewEvent()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('event.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Event');
        $this->assertSelectorTextContains('span.h4', 'New Event');
    }

    public function testIcal()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('event.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/events/1/ical');
        $this->assertResponseIsSuccessful();
    }
}
