# Test REST Server

Stupid simple utility class for creating a local web server suitable for testing REST clients. It starts PHP's built-in web server, which provides an HTTP response complete with status code, headers, and body. You provide these in your PHPUnit test, and then check to make sure you client does what you expect.


## Requirements

* PHP 5.5.0 or higher.
* [Composer](https://getcomposer.org)

## Installation

1. `git https://github.com/mjordan/irc.git`
1. `cd irc`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

Or, use composer:

```
composer require mjordan/irc
```

and within a composer.json file:

```javascript
    "require": {
        "mjordan/irc": "dev-master"
    }
```

## Usage

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


## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

Still in development. Once it's past the proof of concept stage, I'd be happy to take PRs, etc.

## License

The Unlicense
