<?php

declare(strict_types=1);

namespace Psr7VersioningTest;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr7Versioning\VersionMiddleware;
use Psr7Versioning\VersionMiddlewareFactory;
use RuntimeException;

/**
 * @inheritDoc
 * @coversDefaultClass Psr7Versioning\VersionMiddlewareFactory
 */
class VersionMiddlewareFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * Test that the factory returns the expected instance
     *
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
                        'path_regex'   => [],
                        'header_regex' => [],
                        'version'      => [],
                    ],
                ]
            );

        $middleware = (new VersionMiddlewareFactory())($container->reveal());

        $this->assertInstanceOf(VersionMiddleware::class, $middleware);
    }

    /**
     * @return array
     */
    public function invalidConfigProvider(): array
    {
        return [
            'invalid_key'          => [['versionnnnning' => []]],
            'missing_version'      => [
                [
                    'versioning' => [
                        'path_regex'   => [],
                        'header_regex' => [],
                    ],
                ],
            ], /*, 'version' => []*/
            'missing_header_regex' => [
                ['versioning' => ['path_regex' => [], 'version' => []]],
            ], /*, 'header_regex' => []*/
            'missing_path_regex'   => [
                [
                    'versioning' => [
                        'header_regex' => [],
                        'version'      => [],
                    ],
                ],
            ], /*'path_regex' => [], */
        ];
    }

    /**
     * Test that the factory returns the expected instance
     *
     * @dataProvider invalidConfigProvider
     * @covers ::__invoke
     * @param array $invalidConfig
     */
    public function testMissingConfigThrowsException(array $invalidConfig)
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('config')
            ->willReturn($invalidConfig);

        $this->expectException(RuntimeException::class);

        $middleware = (new VersionMiddlewareFactory())($container->reveal());
    }
}
