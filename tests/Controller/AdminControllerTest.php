<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Administration');
        $this->assertSelectorTextContains('span.h4', 'Administration');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/');
        $this->assertResponseStatusCodeSame(403);
    }
}
