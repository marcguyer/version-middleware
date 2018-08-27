<?php

declare(strict_types=1);

namespace MG\Versioning;

use Psr\Container\ContainerInterface;

class VersionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new VersionMiddleware(
            $container->get('config')['api_version']
        );
    }
}
