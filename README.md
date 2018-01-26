# Test REST Server [![Build Status](https://travis-ci.org/mjordan/test_rest_server.svg?branch=master)](https://travis-ci.org/mjordan/test_rest_server)

Simple utility class for creating a local web server suitable for testing REST clients. It uses PHP's built-in web server to provide HTTP responses complete with status code, headers, and body. You provide the details of the expected response when you instantiate the server in your PHPUnit (or SimpleTest, etc.) test. Your client-under-test has complete access to this response. You can also add complex logic to your test server using "templates".

## Requirements

* PHP 5.5.0 or higher (tested on PHP 5.6, 7.0, and 7.1).
* [Composer](https://getcomposer.org)

## Installation

1. `git https://github.com/mjordan/test_rest_server.git`
1. `cd test_rest_server`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

Or, use composer:

```
composer require mjordan/test_rest_server dev-master
```

and within a composer.json file:

```javascript
    "require": {
        "mjordan/test_rest_server": "dev-master"
    }
```

## Usage

To use this test server, you create an instance of `TestRestServer`, which takes four parameters:

* URI (string): A path relative to the root of the server. The URI is ignored by the default server template, but it is useful to provide one as a form of in-code documentation for your test. If you need a server that has to respond differently to different URIs, you can use a custom template that inspects the value of `$_SERVER['REQUEST_URI']` and responds accordingly.
* Response code (int): 200, 201, 401, etc.
* Headers (optional; array of strings): Any headers you want the server to include in the response.
* Body (optional; string): The content of the response body.

```php
$this->server = new TestRestServer('/testing/foo', 201, array('Content-Type: text/plain'), 'Is this thing on?');
```
After you instantiate your server, you start it (using the `start()` method). At this point, HTTP clients then hit the server, which responds with the values you passed it.

### A basic example using PHPUnit

```php
<?php

namespace mjordan\TestRestServer;

use mjordan\TestRestServer\TestRestServer;
// Works with non-Guzzle clients too. It's a real HTTP server!
use GuzzleHttp\Client as GuzzleClient;

// PHPUnit is not the only test tool this will work with. Any PHP test tool is OK.
class ExampleTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $this->server = new TestRestServer('/testing/foo', 201, array('Content-Type: text/plain'), 'Is this thing on?');
        // You can pass a port number into start() if you want. The default is 8001.
        $this->server->start();

        $client = new \GuzzleHttp\Client();
        // Make sure the port number in your request is the same as the
        // one the test server is running on.
        $response = $client->post('http://localhost:8001/testing');
        $response_body = (string) $response->getBody();

        $this->assertEquals('Is this thing on?', $response_body);
        $this->assertEquals(201, $response->getStatusCode());
    }
}
```

PHPUnit's output:

```
PHPUnit 4.8.36-1-g18e5f52 by Sebastian Bergmann and contributors.
.

Time: 5.08 seconds, Memory: 7.25MB

OK (1 test, 2 assertions)
```

### A more useful example

The real usefulness of a test server is that it can be used to test classes that contain REST clients. In other words, you are not testing the client directly, but you are testing code that uses an HTTP client.

Imagine a simple class, Sample. It has one method, `request()`, which uses a REST client to determine the value of a property `foo`:

```php
<?php

namespace mjordan\Sample;

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
use mjordan\Sample\Sample;

class ClassTest extends \PHPUnit\Framework\TestCase
{
    public function testExample()
    {
        $this->server = new TestRestServer('/testing/foo', 200);
        $this->server->start();
 
        $sample = new Sample();
        $sample->request();

        $this->assertEquals('bar', $sample->foo);
    }
}

```

## Using server templates

You can use a custom test server template by passing in a path to a template as the second parameter to `$server->start()` (the first parameter is the port number that the server will run on, by default 8001). The value of the path parameter should be the full path to a Twig template file that outputs PHP code.


* You can do whatever you want within that PHP code.
* You don't need to pass a URI, response code, headers, or body valeus into the template, but you can if you want. Within the template they will be accessible as:
  * `headers` (array)
  * `code` (int)
  * `body` (string)

The URI that the server is responding to is available in `$_SERVER['REQUEST_URI']`.

```php
$uri = '/testing/foo';
$code = 201;
$headers = array('Content-Type: text/plain');
$path_to_template = '/tmp/my_server_template.tpl';

$this->server = new TestRestServer($uri, $code, $headers, '');
$this->server->start('8001', $path_to_template);
```

This is an example of a Twig template for a server that uses one variable, `body`:

```
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['REQUEST_URI'] == '/foo/bar') {
    // Some logic goes here to generate the response code or
    // headers based on $_SERVER variables.

    http_response_code(201);
    header("Content-Type: application/json");
    // We get the body of the request from our TestRestServer instance.
    print json_encode({{ body }});
}
```

## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

Suggestions, use cases, and bug reports welcome. If you want to to open a pull request, please open an issue first.

To run tests, run `composer tests`. To run PSR2 code style checks, run `composer style`.

## License

The Unlicense
