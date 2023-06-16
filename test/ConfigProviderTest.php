<?php

declare(strict_types=1);

namespace Psr7VersioningTest;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr7Versioning\ConfigProvider;
use Psr7Versioning\VersionMiddleware;

use function preg_match;
use function print_r;

class ConfigProviderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /**
     * @return array
     */
    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        $this->assertIsArray($config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     * @param array $config
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);

        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertIsArray($config['dependencies']['factories']);
        $this->assertArrayHasKey(
            VersionMiddleware::class,
            $config['dependencies']['factories'],
            print_r($config, true)
        );
    }

    /**
     * @depends testInvocationReturnsArray
     * @param array $config
     */
    public function testReturnedArrayContainsVersionConfig(array $config)
    {
        $this->assertArrayHasKey('versioning', $config);
        $this->assertIsArray($config['versioning']);

        $config = $config['versioning'];

        $this->assertArrayHasKey('version', $config);
        $this->assertIsArray($config['version']);

        $this->assertArrayHasKey('vendor', $config['version']);
        $this->assertIsString($config['version']['vendor']);
        $this->assertArrayHasKey('version', $config['version']);
        $this->assertIsString($config['version']['version']);
        $this->assertArrayHasKey('resource', $config['version']);
        $this->assertIsString($config['version']['resource']);
        $this->assertArrayHasKey('from', $config['version']);
        $this->assertIsString($config['version']['from']);

        // check for regex and validate
        foreach (['path_regex', 'header_regex'] as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertIsArray($config[$key]);
            foreach ($config[$key] as $r) {
                $this->assertIsString($r);
                $this->assertNotFalse(
                    @preg_match($r, ''),
                    'Regex is invalid'
                );
            }
        }
    }
}
