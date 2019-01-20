<?php

declare(strict_types=1);

namespace Psr7Versioning;

/**
 * The configuration provider for version middleware
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{

    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'versioning' => [
                // regex patterns to use in Psr7Versioning\VersionMiddleware
                // to match path version each regex should include a named
                // subpattern in format (?P<name>) for "version"
                'path_regex' => [
                    '#^/v(?P<version>[^/]+).*$#',
                ],
                // regex patterns to use in Psr7Versioning\VersionMiddleware
                // to match Accept: header each regex should include named
                // subpatterns in format (?P<name>) for "vendor", "version",
                // and "resource"
                'header_regex' => [
                    '#^application/vnd\.(?P<vendor>[^.]+)\.v(?P<version>\d+)(?:\.(?P<resource>[a-zA-Z0-9_-]+))?(?:\+[a-z]+)?$#',
                ],
                // default version settings for Psr7Versioning\VersionMiddleware
                'version' => [
                    'vendor' => 'unk',
                    'version' => '1',
                    'resource' => '',
                    'from' => 'default',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                VersionMiddleware::class => VersionMiddlewareFactory::class,
            ],
        ];
    }
}
