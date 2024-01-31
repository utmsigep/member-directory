<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessengerControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithDCommunicationManagerRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/messenger/email', 302);
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseRedirects('/messenger/email', 302);
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/messenger/');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testEmail()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/messenger/email');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Messenger - Email');
        $this->assertSelectorTextContains('span.display-6', 'Send Bulk Email');
        $this->assertStringContainsString('Cox, Lucian (1-0007)', $crawler->filter('#member_email_recipients')->html());
        $this->assertStringNotContainsString('Wallace, Bill (1-0004)', $crawler->filter('#member_email_recipients')->html());
        $this->assertStringNotContainsString('(1-0013)', $crawler->filter('#member_email_recipients')->html());
    }

    public function testSms()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/messenger/sms');
        if (!isset($_ENV['TWILIO_DSN']) || !$_ENV['TWILIO_DSN']) {
            $this->assertResponseRedirects('/messenger/', 302);

            return;
        }
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Messenger - SMS');
        $this->assertSelectorTextContains('span.display-6', 'Send Bulk SMS');
        $this->assertStringContainsString('Jenkens, Carter (1-0001)', $crawler->filter('#member_sms_recipients')->html());
        $this->assertStringNotContainsString('Gaw, Ben (1-0002)', $crawler->filter('#member_sms_recipients')->html());
        $this->assertStringNotContainsString('(1-0013)', $crawler->filter('#member_sms_recipients')->html());
    }
}
