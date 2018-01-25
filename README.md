# Test REST Server

Simple utility class for creating a local web server suitable for testing REST clients. It uses PHP's built-in web server to provide HTTP responses complete with status code, headers, and body. You provide the details of the expected response when you instantiate the server in your PHPUnit test. Your client-under-test has complete access to this response. You can also add complex logic to your test server using "templates".

## Requirements

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)

## Installation

1. `git https://github.com/mjordan/test_rest_server.git`
1. `cd test_rest_server`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

Or, use composer:

```
composer require mjordan/test_rest_server
```

and within a composer.json file:

```javascript
    "require": {
        "mjordan/test_rest_server": "dev-master"
    }
```

## Usage

To use this test server, you create an instance of `TestRestServer`, which takes four paramameters:

* URI (string): A path releative to the root of the server. The URI is not used by the default server template, but if you need a server that does respond differently to different incoming URIs, you can use a custom template that inspects the value of `$_SERVER['REQUEST_URI']` and responds accordingly.
* Response code (int): 200, 201, 401, etc.
* Headers (optional; array of strings): Any headers you want the server to include in the response.
* Body (optional; string): The content of the response body.
* Path to template (optional; string): The full path to your custom server (Twig) template file.

```php
$this->server = new TestRestServer('/testing/foo', 201, array('Content-Type: text/plain'), 'Is this thing on?');
```
HTTP clients then hit the server, which responds with the values you passed it.

### A basic example

```php
<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use GuzzleHttp\Client as GuzzleClient;

// PHPUnit is not the only test tool this will work with. Any PHP test tool is OK.
class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $this->server = new TestRestServer('/testing/foo', 201, array('Content-Type: text/plain'), 'Is this thing on?');
        // You can pass a port number into start() if you want. The default is 8001.
        $this->server->start();

        // Works with non-Guzzle clients too. It's real HTTP!
        $client = new \GuzzleHttp\Client();
        $response = $client->post('http://localhost:8001//testing');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Is this thing on?', $response_body);
        $this->assertEquals(201, $response->getStatusCode());
    }
}
```

```
PHPUnit 4.8.36-1-g18e5f52 by Sebastian Bergmann and contributors.
.

Time: 5.08 seconds, Memory: 7.25MB

OK (1 test, 2 assertions)
```

### A more realistic example

The real usefulness of a test server is it can be used to test classes that contain REST clients.

```php
<?php

namespace mjordan\TestRestServer;

use GuzzleHttp\Client as GuzzleClient;

/**
 * Test REST Server Sample Class.
 */
class Sample
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    public function request()
    {
        $response = $this->client->get('http://localhost:8001/somepath');
        if ($response->getStatusCode() == 200) {
            $this->foo = 'bar';
        }
    }
}
```

The test:

```php
<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
use mjordan\TestRestServer\SampleClass;

class SampleClassTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $this->server = new TestRestServer('/testing/foo', 200);
        $this->server->start();

        // The REST client is within the SampleClass class. 
        $sample = new SampleClass();
        $sample->request();

        $this->assertEquals('bar', $sample->foo);
    }
}
```

## Using your own server templates

You can set your test server's template using `$server->template($path);`. The value should be the full file path to a template file. The template itself is a Twig template that outputs PHP code.

* You can do whatever you want within that code.
* You don't need to pass a URI, response code, headers, or body valeus into the template, but you can if you want. Within the template they will be accessible as:
  * `headers` (array)
  * `code` (int)
  * `body` (string)

```php
$uri = '/testing/foo';
$code = 201;
$headers = array('Content-Type: text/plain');
$path_to_template = '/tmp/my_server_template.tpl';

$this->server = new TestRestServer($uri, $code, $headers, '', $path_to_template);
```

```
<?php

// This is actually a Twig template with no Twig variables.

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Some logic goes here to generate the response code, body or
    // headers based on $_SERVER variables.

    http_response_code(200);
    header("Content-Type: application/json");
    print json_encode($body);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Some logic goes here to generate the response code, body or
    // headers based on $_SERVER variables.

    http_response_code(201);
    header("Content-Type: application/json");
    print json_encode($body);
}
```

## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

Still in development. Once it's past the proof of concept stage, I'd be happy to take PRs, etc.

## License

The Unlicense
