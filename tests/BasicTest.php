<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class BasicTest extends \PHPUnit\Framework\TestCase
{
    public function testBasic()
    {
        $this->server = new TestRestServer('/testing/foo', 201, array(), 'Is this thing on?');
        $this->server->start();

        $client = new \GuzzleHttp\Client();
        $response = $client->post('http://localhost:8001/testing');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Is this thing on?', $response_body);
        $this->assertEquals(201, $response->getStatusCode());
    }
}
