<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/users/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Users');
        $this->assertSelectorTextContains('span.display-6', 'Users');
    }

    public function testShowWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/users/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Basic User (user@example.com)');
        $this->assertSelectorTextContains('span.display-6', 'Basic User (user@example.com)');
    }

    public function testEditWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/users/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Basic User (user@example.com)');
        $this->assertSelectorTextContains('span.display-6', 'Basic User (user@example.com)');
    }

    public function testNewWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/users/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New User');
        $this->assertSelectorTextContains('span.display-6', 'New User');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/users/');
        $this->assertResponseStatusCodeSame(403);
    }
}
