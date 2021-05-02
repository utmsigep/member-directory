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

    public function testShowDirectoryCollection()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/collection/member');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span', 'Member');
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
}
