<?php

declare(strict_types=1);

namespace Psr7Versioning;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Factory for VersionMiddleware
 */
class VersionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): VersionMiddleware
    {
        $config = $container->get('config')['versioning'] ?? null;
        if (null === $config) {
            throw new RuntimeException(
                'Config key \'versioning\' missing'
            );
        }
        if (! isset($config['version'])) {
            throw new RuntimeException(
                'Default version config missing'
            );
        }
        if (! isset($config['path_regex'])) {
            throw new RuntimeException(
                'Version config path regex missing'
            );
        }
        if (! isset($config['header_regex'])) {
            throw new RuntimeException(
                'Version config header regex missing'
            );
        }

        return new VersionMiddleware($config);
    }
}
