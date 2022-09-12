<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImportControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testShowImport()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/import/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member Import');
        $this->assertSelectorTextContains('span.display-6', 'Member Import');
    }

    public function testShowImportWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/import/');
        $this->assertResponseStatusCodeSame(403);
    }
}
