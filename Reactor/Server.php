<?php


namespace Jogaram\ReactPHPBundle\Reactor;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;

class Server {

    private $port = 1337;
    private $debug = false;
    private $standalone = false;
    private $root_dir;

    /** @var LoopInterface */
    private $loop;
    /** @var SocketServer */
    private $socket;

    function __construct($root_dir, $port = 1337)
    {
        $this->port = $port;
        $this->root_dir = $root_dir;

        return $this;
    }


    public function build(){
        require_once $this->root_dir . '/AppKernel.php';
        define('KERNEL_ROOT', $this->root_dir);

        $kernel = new ReactKernel($this->debug ? 'dev' : 'prod', $this->debug ? true : false);

        $this->loop = Factory::create();
        $this->socket = new SocketServer($this->loop);
        $http = new HttpServer($this->socket, $this->loop);

        if ($this->standalone) {
            $http->on('request', $this->handleRequest($kernel));
        } else {
            $http->on('request', $kernel);
        }

        return $this;
    }

    public function run(){
        $this->socket->listen($this->port);
        $this->loop->run();
    }

    private function handleRequest(ReactKernel $kernel) {
        return function (Request $request, Response $response) use ($kernel) {
            $file = $this->root_dir . '/../web' . $request->getPath();
            if ($request->getPath() !== '/' && file_exists($file)) {
                $response->writeHead(200, array(
                    'Content-Type' => $this->getFileMimeType($file),
                    'Content-Length' => filesize($file)
                ));
                $response->end(file_get_contents($file));
            } else {
                $kernel($request, $response);
            }
        };
    }

    private function getFileMimeType($path) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'css':
                return 'text/css';
                break;
            case 'js':
                return 'text/javascript';
                break;
            default:
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime =  finfo_file($finfo, $path);
                finfo_close($finfo);

                return $mime;
        }
    }

    /**
     * @param boolean $debug
     * @return Server
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param boolean $standalone
     * @return Server
     */
    public function setStandalone($standalone)
    {
        $this->standalone = $standalone;
        return $this;
    }
}