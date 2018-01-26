<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        // We pass in a response code of 200 but our template is going to return 201.
        $this->server = new TestRestServer('/testing/foo', 200, array(), 'Hi!');
        $template_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' .
            DIRECTORY_SEPARATOR . 'my_server_template.tpl';
        $this->server->start('8001', $template_path);

        $client = new \GuzzleHttp\Client();
        $response = $client->post('http://localhost:8001/testing/foo');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Hi!', $response_body);
        $this->assertEquals(201, $response->getStatusCode());
    }
}
