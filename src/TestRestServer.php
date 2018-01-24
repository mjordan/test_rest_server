<?php

namespace mjordan\TestRestServer;

use Cocur\BackgroundProcess\BackgroundProcess;
use Twig\Twig;

/**
 * Islandora REST Client TestRestServer class.
 */
class TestRestServer
{
    /**
     * Constructor.
     */
    public function __construct($uri, $code, $headers = array(), $body = '')
    {
        $this->router_path = sys_get_temp_dir() . '/TestRestServer.php';

        $response['uri'] = $uri;
        $response['code'] = $code;
        $response['headers'] = $headers;
        $response['body'] = $body;

        $loader = new \Twig_Loader_Filesystem(__DIR__);
        $twig = new \Twig_Environment($loader);
        $router_php_code = $twig->render('server_template.tpl', $response);

        file_put_contents($this->router_path, $router_php_code);
    }

    public function __destruct()
    {
        if ($this->process->isRunning()) {
            $this->process->stop();
        }

        @unlink($this->router_path);
    }

    public function start($port = '8001')
    {
        $this->process = new BackgroundProcess('php -S localhost:' . $port . ' ' . $this->router_path);
        $this->process->run();
        $this->processId = $this->process->getPid();
        sleep(5);
    }
}
