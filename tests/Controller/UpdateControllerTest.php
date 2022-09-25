<?php

namespace App\Tests\Controller;

use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpdateControllerTest extends WebTestCase
{
    public function testUpdateMemberRecord(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(MemberRepository::class);
        $testMember = $userRepository->findOneByPrimaryEmail('unclebilly@example.org');

        $crawler = $client->request('GET', sprintf(
            '/update-my-info/d793c0b9b023ea082dea7885cc09268d/%s',
            $testMember->getUpdateToken()
        ));

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Update My Member Record');
        $this->assertSelectorTextContains('div.h4', 'Update My Member Record');
        $this->assertSelectorTextContains('div.alert', 'Hello Billy Phillips! We have pre-loaded your information for you. This link will expire once the update is processed.');
    }
}
