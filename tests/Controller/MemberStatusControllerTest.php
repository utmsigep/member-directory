<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberStatusControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/member-statuses/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/member-statuses/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member Statuses');
        $this->assertSelectorTextContains('span.h4', 'Member Statuses');
    }

    public function testShowWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/member-statuses/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.h4', 'Member');
    }

    public function testEditWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/member-statuses/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.h4', 'Member');
    }

    public function testNewWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/member-statuses/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Member Status');
        $this->assertSelectorTextContains('span.h4', 'New Member Status');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/member-statuses/');
        $this->assertResponseStatusCodeSame(403);
    }
}
