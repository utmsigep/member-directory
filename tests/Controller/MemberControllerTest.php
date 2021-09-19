<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MemberControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testShowMember()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testMessageMember()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/message');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins - Send Message');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testMessageMemberSendEmail()
    {
        if (!isset($_ENV['MAILER_DDSN']) || 'smtp://localhost' === $_ENV['MAILER_DSN']) {
            $this->markTestSkipped('Email not configured.');

            return;
        }

        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/directory/member/1-0001/message');
        $emailForm = $crawler->filter('form[name="member_email"]')->form();
        $emailForm->setValues([
            'member_email[subject]' => 'Test Member Message',
            'member_email[message_body]' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.',
        ]);

        $crawler = $client->submit($emailForm);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert', 'Email message sent!');
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($email, 'Subject', 'Test Member Message');
        $this->assertEmailTextBodyContains($email, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.');
    }

    public function testMessageMemberSendSms()
    {
        if (!isset($_ENV['TWILIO_DSN']) || !$_ENV['TWILIO_DSN']) {
            $this->markTestSkipped('Twilio not configured.');

            return;
        }

        $client = static::createClient();
        $client->enableProfiler();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/directory/member/1-0001/message');
        $emailForm = $crawler->filter('form[name="member_sms"]')->form();
        $emailForm->setValues([
            'member_sms[message_body]' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.',
        ]);

        $crawler = $client->submit($emailForm);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert', 'SMS message sent!');
        if ($profile = $client->getProfile()) {
            $collector = $profile->getCollector('notifier');
            $messages = $collector->getEvents()->getMessages();
            $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor.', $messages[0]->getSubject());
            $this->assertEquals('(804) 353-1901', $messages[0]->getPhone());
        }
    }

    public function testShowChangeLog()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/change-log');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins - Change Log');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testShowChangeLogWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/change-log');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testGenerateVcard()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/vcard');
        $this->assertResponseIsSuccessful();
    }

    public function testShowMemberDonations()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/donations');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins - Donations');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testShowMemberDonationsWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/donations');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowMemberCommunications()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('communications.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/communications');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins - Communications');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testShowMemberCommunicationsWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/communications');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditMember()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Carter Jenkins');
        $this->assertSelectorTextContains('span.h4', 'Carter Jenkins');
    }

    public function testEditMemberWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/1-0001/edit');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNewMember()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('directory.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Member');
        $this->assertSelectorTextContains('span.h4', 'New Member');
    }

    public function testNewMemberWithoutRole()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/directory/member/new');
        $this->assertResponseStatusCodeSame(403);
    }
}
