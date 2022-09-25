<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DonationControllerTest extends WebTestCase
{
    public function testLoginRequired()
    {
        $client = static::createClient();
        $client->request('GET', '/donations/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testIndexWithDonationManagerRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Donations');
        $this->assertSelectorTextContains('span.display-6', 'Donations');
    }

    public function testIndexWithAdminRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Donations');
        $this->assertSelectorTextContains('span.display-6', 'Donations');
    }

    public function testDenyWithUserRole()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowDonation()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/1');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - #11011901-001 - Phillips, Billy (1-0006) @ 2016-11-01 (1000.00 USD)');
        $this->assertSelectorTextContains('span.display-6', '#11011901-001 - Phillips, Billy (1-0006) @ 2016-11-01 (1000.00 USD)');
    }

    public function testEditDonation()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/1/edit');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - #11011901-001 - Phillips, Billy (1-0006) @ 2016-11-01 (1000.00 USD)');
        $this->assertSelectorTextContains('span.display-6', '#11011901-001 - Phillips, Billy (1-0006) @ 2016-11-01 (1000.00 USD)');
    }

    public function testNewDonation()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/new');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - New Donation');
        $this->assertSelectorTextContains('span.display-6', 'New Donation');
    }

    public function testDonorsList()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/donors');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Donors');
        $this->assertSelectorTextContains('span.display-6', 'Donors');
    }

    public function testCampaignsList()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('donation.manager@example.com');
        $client->loginUser($testUser);

        $client->request('GET', '/donations/campaigns');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Campaigns');
        $this->assertSelectorTextContains('span.display-6', 'Campaigns');
    }
}
