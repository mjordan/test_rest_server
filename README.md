# Test REST Server

Simple utility class for creating a local web server suitable for testing REST clients. It uses PHP's built-in web server to provide HTTP responses complete with status code, headers, and body. You provide the details of the expected response in your PHPUnit test, which your client-under-test has complete access to. You can also add complex logic to your test server using "templates".


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

### A basic example

```php
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

If you pass in a full path to a Twig template as the fourth parameter to the TestRestServer(), that template will be used to return the HTTP responses:

They are Twig templates that contain PHP code. You don't need to pass any variables into the template though.

```php
$uri = '/testing/foo';
$code = 201;
$headers = array('X-Authorization-User: foo:bar');
$body = '';
$path_to_template = '/tmp/my_server_template.tpl';

$this->server = new TestRestServer($uri, $code, $headers, $body, $path_to_template);
```

This template doesn't need to use the first three parameters, although it can:

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
