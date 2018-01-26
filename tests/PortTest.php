<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use GuzzleHttp\Client as GuzzleClient;

class PortTest extends \PHPUnit\Framework\TestCase
{
    public function testPort()
    {
        $this->server = new TestRestServer('/testing_ports', 200, array(), 'Is this port working?');
        $this->server->start('8002');

        $client = new \GuzzleHttp\Client();
        $response = $client->get('http://localhost:8002/testing_ports');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Is this port working?', $response_body);
    }
}
