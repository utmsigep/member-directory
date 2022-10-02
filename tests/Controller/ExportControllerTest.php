<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExportControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testShowExport()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/export/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Member Export');
        $this->assertSelectorTextContains('span.display-6', 'Member Export');
    }
}
