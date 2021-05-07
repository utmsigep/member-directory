<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/directory/search/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testEmptySearchPage()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/search/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Search Results');
        $this->assertSelectorTextContains('span.h4', 'Search Results');
        $this->assertSelectorTextContains('div.h3', 'No Members matched your search criteria.');
    }

    public function testMatchingSearchPage()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/search/?q=Carter');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter');
        $this->assertSelectorTextContains('span.h4', 'Search Results');
        $this->assertSelectorTextContains('div.card-body > a', 'Carter Jenkins');
    }

    public function testEmptyAutoComplete()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/search/autocomplete?q=Foobarbaz');
        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(0, count($jsonData));
    }

    public function testMatchingAutoComplete()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/search/autocomplete?q=Carter');
        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(2, count($jsonData));
        $this->assertEquals('1-0001', $jsonData[0]->localIdentifier);
        $this->assertEquals('Carter Jenkins', $jsonData[0]->displayName);
    }
}
