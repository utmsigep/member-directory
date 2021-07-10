<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginRedirect()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login', 302);
    }

    public function testViewLoginPage()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertPageTitleSame('Member Directory - Log In');
        $this->assertResponseIsSuccessful();
    }

    public function testSubmitValidLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $crawler = $client->submitForm('Sign in', ['email' => 'admin@example.com', 'password' => 'testing']);
        $this->assertResponseRedirects('/directory/', 302);
    }

    public function testSubmitInvalidLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $crawler = $client->submitForm('Sign in', ['email' => 'admin@example.com', 'password' => 'wrongpassword']);
        $this->assertResponseRedirects('/login', 302);
    }

    public function test2FALogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $crawler = $client->submitForm('Sign in', ['email' => 'user.2fa@example.com', 'password' => 'testing']);
        $this->assertResponseRedirects('/directory/', 302);
        $client->followRedirect();
        $this->assertResponseRedirects('http://localhost/2fa', 302);
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Member Directory - Log In');
        $this->assertSelectorTextContains('div.h3', 'Your Two-Factor Code');
    }
}
