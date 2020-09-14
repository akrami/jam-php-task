<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class InvitationTest extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'localhost:3030'
        ]);
        parent::setUp();
    }

    public function testWelcome()
    {

        $response = $this->client->request('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals(1, $json['status']);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals('welcome', $json['message']);
    }

    public function testLogin()
    {
        /*
         * correct login
         */
        $response = $this->client->request('POST', '/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals(1, $json['status']);
        $this->assertArrayHasKey('token', $json);

        /*
         * failed login
         */
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $response = $this->client->request('POST', '/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'wrongpassword'
            ]
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $json = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals(0, $json['status']);
        $this->assertArrayHasKey('error', $json);
        $this->assertEquals('authentication failed', $json['error']);

    }

}
