<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $this->server = new TestRestServer('/testing/foo', 201, array('X-Authorization-User: foo:bar'), 'Is this thing on?');
        $this->server->start();

        $client = new \GuzzleHttp\Client();
        $response = $client->post('http://localhost:8001//testing');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Is this thing on?', $response_body);
        $this->assertEquals(201, $response->getStatusCode());
    }
}