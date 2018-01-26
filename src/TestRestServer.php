<?php

namespace mjordan\TestRestServer;

use Cocur\BackgroundProcess\BackgroundProcess;
use Twig\Twig;

/**
 * Test REST Server TestRestServer class.
 */
class TestRestServer
{
    /**
     * @var string
     */
    protected $router_path;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var object
     */
    protected $process;

    /**
     * Constructor.
     */
    public function __construct($uri, $code, $headers = array(), $body = '')
    {
        $this->router_path = sys_get_temp_dir() . '/TestRestServer.php';

        $this->response['uri'] = $uri;
        $this->response['code'] = $code;
        $this->response['headers'] = $headers;
        $this->response['body'] = $body;
    }

    public function __destruct()
    {
        if ($this->process->isRunning()) {
            $this->process->stop();
        }

        @unlink($this->router_path);
    }

    public function start($port = '8001', $path_to_template = 'server_template.tpl')
    {
        if ($path_to_template == 'server_template.tpl') {
            $template_dir = __DIR__;
        } else {
            $template_dir = dirname($path_to_template);
        }

        $loader = new \Twig_Loader_Filesystem($template_dir);
        $twig = new \Twig_Environment($loader);
        $router_php_code = $twig->render(basename($path_to_template), $this->response);

        file_put_contents($this->router_path, $router_php_code);

        $this->process = new BackgroundProcess('php -S localhost:' . $port . ' ' . $this->router_path);
        $this->process->run();
        $this->processId = $this->process->getPid();
        sleep(5);
    }
}
