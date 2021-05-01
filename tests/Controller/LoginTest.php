<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLoginRedirect()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testViewLoginPage()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSubmitValidLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $crawler = $client->submitForm('Sign in', ['email' => 'admin@example.com', 'password' => 'testing']);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/directory/', $client->getResponse()->getTargetUrl());
    }

    public function testSubmitInvalidLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $crawler = $client->submitForm('Sign in', ['email' => 'admin@example.com', 'password' => 'wrongpassword']);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $client->getResponse()->getTargetUrl());
    }
}
