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

    public function testShowVcard()
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
