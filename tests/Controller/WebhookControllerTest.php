<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/webhook');

        $this->assertResponseIsSuccessful();
        $jsonData = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $jsonData->status);
        $this->assertEquals('success', $jsonData->title);
        $this->assertEquals('Webhooks are available.', $jsonData->details);
    }
}
