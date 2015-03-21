<?php


namespace Jogaram\ReactPHPBundle\Reactor;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;
use Composer\Autoload\ClassLoader;
use Symfony\Component\ClassLoader\ApcClassLoader;

class Server {

    private $port = 1337;
    private $env = 'dev';
    private $apc = false;
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

    /**
     * Builds internal request handling objects.
     *
     * @return $this
     */
    public function build(){
        $loader = new ClassLoader();

        if ($this->apc) {
            $apcLoader = new ApcClassLoader(sha1('ReactServer'), $loader);
            $loader->unregister();
            $apcLoader->register(true);
        }

        require_once $this->root_dir . '/AppKernel.php';
        define('KERNEL_ROOT', $this->root_dir);

        $kernel = new ReactKernel($this->env, $this->env === 'dev' ? true : false);

        $this->loop = Factory::create();
        $this->socket = new SocketServer($this->loop);
        $http = new HttpServer($this->socket, $this->loop);
        $http->on('request', $this->handleRequest($kernel));

        return $this;
    }

    /**
     * Runs the server by initializing ReactPHP EventLoop
     *
     * @throws \React\Socket\ConnectionException
     */
    public function run(){
        $this->socket->listen($this->port);
        $this->loop->run();
    }

    /**
     * Handles a request. In case of standalone mode is active, it directly serves static files
     * from file system.
     *
     * @param ReactKernel $kernel
     * @return callable|ReactKernel
     */
    private function handleRequest(ReactKernel $kernel) {
        if (!$this->standalone)
            return $kernel;
        else
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

    /**
     * Guess mime type for a given path in HTTP header type.
     *
     * @param $path
     * @return mixed|string
     */
    private function getFileMimeType($path) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'css':
                return 'text/css';
            case 'js':
                return 'text/javascript';
            default:
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime =  finfo_file($finfo, $path);
                finfo_close($finfo);

                return $mime;
        }
    }

    /**
     * @param string $env
     * @return Server
     */
    public function setEnv($env)
    {
        $this->env = $env;
        return $this;
    }

    /**
     * @param boolean $apc
     * @return Server
     */
    public function setApc($apc)
    {
        $this->apc = $apc;
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