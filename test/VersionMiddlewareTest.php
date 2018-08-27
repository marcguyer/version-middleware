<?php

namespace MGTest\Versioning;

use MG\Versioning\VersionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class VersionMiddlewareTest extends TestCase
{
    public function testVersionFromPath()
    {
        $server = [
            'SERVER_PROTOCOL' => '1.1',
            'HTTP_HOST' => 'example.com',
            'HTTP_ACCEPT' => 'application/json',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/v2/foo/bar',
            'QUERY_STRING' => 'bar=baz',
        ];
        $request = ServerRequestFactory::fromGlobals($server, [], [], [], []);

        $middleware = new VersionMiddleware();
    }
}
