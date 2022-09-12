<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DirectoryCollectionControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/directory-collections/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/directory-collections/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Directory Collections');
        $this->assertSelectorTextContains('span.display-6', 'Directory Collections');
    }

    public function testShowWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/directory-collections/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.display-6', 'Member');
    }

    public function testEditWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/directory-collections/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member');
        $this->assertSelectorTextContains('span.display-6', 'Member');
    }

    public function testNewWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/directory-collections/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Directory Collection');
        $this->assertSelectorTextContains('span.display-6', 'New Directory Collection');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/directory-collections/');
        $this->assertResponseStatusCodeSame(403);
    }
}
