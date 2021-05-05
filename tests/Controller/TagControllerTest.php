<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/tags/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/tags/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Tags');
        $this->assertSelectorTextContains('span.h4', 'Tags');
    }

    public function testShowWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/tags/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 1901 Club');
        $this->assertSelectorTextContains('span.h4', '1901 Club');
    }

    public function testEditWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/tags/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - 1901 Club');
        $this->assertSelectorTextContains('span.h4', '1901 Club');
    }

    public function testNewWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/tags/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Tag');
        $this->assertSelectorTextContains('span.h4', 'New Tag');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/tags/');
        $this->assertResponseStatusCodeSame(403);
    }
}
