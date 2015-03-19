<?php

namespace Jogaram\ReactPHPBundle\Reactor;

use React\Http\Request;
use React\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Config\Loader\LoaderInterface;

class ReactKernel extends \AppKernel
{
    public function __invoke(Request $request, Response $response)
    {
        $this->loadClassCache();

        if ($request->getMethod() === 'POST') {
            $request->on('data', function ($postData) use ($request, $response) {
                parse_str($postData, $postDataArray);
                $this->doRequest($request, $response, $postDataArray);
            });
        } else {
            $this->doRequest($request, $response);
        }
    }

    private function doRequest(Request $request, Response $response, array $parameters = array()) {
        $sfRequest = SymfonyRequest::create($request->getPath(), $request->getMethod());

        $sfRequest->headers->replace($request->getHeaders());
        $sfRequest->request->replace($parameters);
        $sfRequest->server->set('REQUEST_URI', $request->getPath());
        $sfRequest->server->set('SERVER_NAME', 'react');

        $sfResponse = $this->handle($sfRequest);
        $this->terminate($sfRequest, $sfResponse);

        $headers = array_map('current', $sfResponse->headers->all());
        $response->writeHead($sfResponse->getStatusCode(), $headers);
        $response->end($sfResponse->getContent());
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    public function getRootDir()
    {
        return KERNEL_ROOT;
    }
}
