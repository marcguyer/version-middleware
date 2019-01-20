<?php

declare(strict_types=1);

namespace Psr7VersioningTest;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr7Versioning\VersionMiddlewareFactory;
use Psr7Versioning\VersionMiddleware;

/**
 * @inheritDoc
 * @coversDefaultClass Psr7Versioning\VersionMiddlewareFactory
 */
class VersionMiddlewareFactoryTest extends TestCase
{

    /**
     * Test that the factory returns the expected instance
     * @covers ::__invoke
     * @covers Psr7Versioning\VersionMiddleware::__construct
     */
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')
            ->willReturn(
                [
                    'versioning' => [
                        'path_regex' => [],
                        'header_regex' => [],
                        'version' => [],
                    ]
                ]
            );

        $middleware = (new VersionMiddlewareFactory())($container->reveal());

        $this->assertInstanceOf(VersionMiddleware::class, $middleware);
    }

    /**
     * @return array
     */
    public function invalidConfigProvider(): array {
        return [
            'invalid_key' => [['versionnnnning' => []]],
            'missing_version' => [['versioning' => ['path_regex' => [], 'header_regex' => []/*, 'version' => []*/]]],
            'missing_header_regex' => [['versioning' => ['path_regex' => []/*, 'header_regex' => []*/, 'version' => []]]],
            'missing_path_regex' => [['versioning' => [/*'path_regex' => [], */'header_regex' => [], 'version' => []]]],
        ];
    }

    /**
     * Test that the factory returns the expected instance
     * @dataProvider invalidConfigProvider
     * @covers ::__invoke
     * @param array $invalidConfig
     */
    public function testMissingConfigThrowsException(array $invalidConfig)
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')
            ->willReturn($invalidConfig);

        $this->expectException(\RuntimeException::class);

        $middleware = (new VersionMiddlewareFactory())($container->reveal());
    }
}
