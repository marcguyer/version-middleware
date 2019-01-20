<?php

namespace Psr7VersioningTest;

use PHPUnit\Framework\TestCase;
use Psr7Versioning\ConfigProvider;
use Psr7Versioning\VersionMiddleware;

class ConfigProviderTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    /**
     * @return array
     */
    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     * @param array $config
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);

        $this->assertArrayHasKey('factories', $config['dependencies']);
        $this->assertInternalType('array', $config['dependencies']['factories']);
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
        $this->assertInternalType('array', $config['versioning']);

        $config = $config['versioning'];

        $this->assertArrayHasKey('version', $config);
        $this->assertInternalType('array', $config['version']);

        $this->assertArrayHasKey('vendor', $config['version']);
        $this->assertInternalType('string', $config['version']['vendor']);
        $this->assertArrayHasKey('version', $config['version']);
        $this->assertInternalType('string', $config['version']['version']);
        $this->assertArrayHasKey('resource', $config['version']);
        $this->assertInternalType('string', $config['version']['resource']);
        $this->assertArrayHasKey('from', $config['version']);
        $this->assertInternalType('string', $config['version']['from']);

        // check for regex and validate
        foreach (['path_regex', 'header_regex'] as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertInternalType('array', $config[$key]);
            foreach ($config[$key] as $r) {
                $this->assertInternalType('string', $r);
                $this->assertNotFalse(
                    @preg_match($r, null),
                    'Regex is invalid'
                );
            }
        }
    }
}
