<?php

namespace Psr7Versioning;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr7Versioning\VersionMiddleware;
use Psr7Versioning\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\ServerRequest;

/**
 * @coversDefaultClass \Psr7Versioning\VersionMiddleware
 */
class VersionMiddlewareTest extends TestCase
{

    /**
     * @return VersionMiddleware
     */
    private function getMiddleware() : VersionMiddleware
    {
        $config = (new ConfigProvider())()['versioning'];
        return new VersionMiddleware($config);
    }

    /**
     * Mock a PSR request.
     * @return ObjectProphecy
     */
    private function getMockRequest(): ObjectProphecy
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        return $request;
    }

    /**
     * @covers ::fromDefaults
     */
    public function testVersionFromDefaults()
    {
        $request = new ServerRequest();

        $middleware = $this->getMiddleware();

        $result = $middleware->fromDefaults($request);

        $this->assertNotSame($result, $request);

        $this->assertInstanceOf(ServerRequestInterface::class, $result);

        $this->assertIsArray($result->getAttribute(VersionMiddleware::class));

        $this->assertEquals(
            1,
            $result->getAttribute(VersionMiddleware::class)['version']
        );
        $this->assertEquals(
            'unk',
            $result->getAttribute(VersionMiddleware::class)['vendor']
        );
        $this->assertEquals(
            '',
            $result->getAttribute(VersionMiddleware::class)['resource']
        );
        $this->assertEquals(
            'default',
            $result->getAttribute(VersionMiddleware::class)['from']
        );
    }

    /**
     * @return array
     */
    public function pathVersionProvider(): array
    {
        return [
            ['/no_version', 1, 'default'],
            ['/v2', 2],
            ['/v2/', 2],
            ['/v2/foo', 2],
            ['/v2/foo/bar', 2],
            ['/v3', 3],
            ['/v3/', 3],
            ['/v3/foo', 3],
            ['/v3/foo/bar', 3],
        ];
    }

    /**
     * @dataProvider pathVersionProvider
     * @param string $path
     * @param int $version
     * @param string $source
     * @covers ::fromPath
     * @covers ::extractVersionFromPath
     */
    public function testVersionFromPath(
        string $path,
        int $version,
        string $source = 'path'
    ) {
        $request = new ServerRequest([], [], $path);

        $middleware = $this->getMiddleware();

        $result = $middleware->fromPath($request);

        $this->assertNotSame($result, $request);

        $this->assertInstanceOf(ServerRequestInterface::class, $result);

        $this->assertIsArray($result->getAttribute(VersionMiddleware::class));

        $this->assertEquals(
            $version,
            $result->getAttribute(VersionMiddleware::class)['version']
        );
        $this->assertEquals(
            'unk',
            $result->getAttribute(VersionMiddleware::class)['vendor']
        );
        $this->assertEquals(
            '',
            $result->getAttribute(VersionMiddleware::class)['resource']
        );
        $this->assertEquals(
            $source,
            $result->getAttribute(VersionMiddleware::class)['from']
        );
    }

    /**
     * @return array
     */
    public function headerVersionProvider(): array
    {
        return [
           [
               '', // empty
               'unk',
               1,
               '',
               'default'
           ],
          [
              'no_header', // invalid
              'unk',
              1,
              '',
              'default'
          ],
           [
               'application/vnd.chdr.v1.status',
               'chdr',
               1,
               'status',
           ],
           [
               'application/vnd.zend.v2.user',
               'zend',
               2,
               'user',
           ],
       ];
    }

    /**
     * @dataProvider headerVersionProvider
     * @param string $header
     * @param string $vendor
     * @param int $version
     * @param string $resource
     * @param string $source
     * @covers ::fromAcceptHeader
     * @covers ::extractVersionFromAcceptHeader
     */
    public function testVersionFromHeader(
       string $header,
       string $vendor,
       int $version,
       string $resource,
       string $source = 'header'
    ) {
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            $header ? ['accept' => $header] : []
        );

        $middleware = $this->getMiddleware();

        $result = $middleware->fromAcceptHeader($request);

        $this->assertNotSame($result, $request);

        $this->assertInstanceOf(ServerRequestInterface::class, $result);

        $this->assertIsArray($result->getAttribute(VersionMiddleware::class));

        $this->assertEquals(
            $version,
            $result->getAttribute(VersionMiddleware::class)['version']
        );
        $this->assertEquals(
            $vendor,
            $result->getAttribute(VersionMiddleware::class)['vendor']
        );
        $this->assertEquals(
            $resource,
            $result->getAttribute(VersionMiddleware::class)['resource']
        );
        $this->assertEquals(
            $source,
            $result->getAttribute(VersionMiddleware::class)['from']
        );
    }

    /**
     * @depends testVersionFromPath
     * @dataProvider pathVersionProvider
     * @param string $path
     * @param int $version
     * @param string|null $source
     * @covers ::process
     * @covers ::hasVersionInPath
     */
    public function testVersionInPath(
        string $path,
        int $version,
        ?string $source = 'path'
    ) {
        $request = new ServerRequest(
            [],
            [],
            $path
        );
        $response = $this->prophesize(ResponseInterface::class);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->will([$response, 'reveal']);

        $middleware = $this->getMiddleware();
        $result = $middleware->process($request, $handler->reveal());
        $this->assertSame($response->reveal(), $result);
    }

    /**
     * @depends testVersionFromHeader
     * @dataProvider headerVersionProvider
     * @param string $header
     * @param string $vendor
     * @param int $version
     * @param string $resource
     * @param string|null $source
     * @covers ::process
     * @covers ::hasVersionInAcceptHeader
     */
    public function testVersionInHeader(
       string $header,
       string $vendor,
       int $version,
       string $resource,
       ?string $source = 'header'
    ) {
        $request = new ServerRequest(
            [],
            [],
            null,
            null,
            'php://input',
            ['accept' => $header]
        );
        $response = $this->prophesize(ResponseInterface::class);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler
            ->handle(Argument::type(ServerRequest::class))
            ->will([$response, 'reveal']);

        $middleware = $this->getMiddleware();
        $result = $middleware->process($request, $handler->reveal());
        $this->assertSame($response->reveal(), $result);
    }
}
