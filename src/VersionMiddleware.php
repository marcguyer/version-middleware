<?php

declare(strict_types=1);

namespace Psr7Versioning;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_merge;
use function array_shift;
use function explode;
use function preg_match;

/**
 * Middleware for managing app versions
 */
class VersionMiddleware implements MiddlewareInterface
{
    /** @var array */
    protected $versionDefaults;

    /** @var array */
    protected $pathRegex;

    /** @var array */
    protected $headerRegex;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->versionDefaults = $config['version'];
        $this->pathRegex       = $config['path_regex'];
        $this->headerRegex     = $config['header_regex'];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // if the path has the version, it takes precedence
        if ($this->hasVersionInPath($request)) {
            return $handler->handle($this->fromPath($request));
        }

        if ($this->hasVersionInAcceptHeader($request)) {
            return $handler->handle($this->fromAcceptHeader($request));
        }

        return $handler->handle($this->fromDefaults($request));
    }

    public function fromPath(ServerRequestInterface $request): ServerRequestInterface
    {
        $matches = $this->extractVersionFromPath($request);

        // if no version info in the path, return with defaults
        if (! $matches) {
            return $this->fromDefaults($request);
        }

        $version = array_merge(
            $this->versionDefaults,
            [
                'version' => $matches[1],
                'from'    => 'path',
            ]
        );

        // return the modified request
        return $request
            // add an attribute with the requested version
            ->withAttribute(self::class, $version);
    }

    public function fromAcceptHeader(ServerRequestInterface $request): ServerRequestInterface
    {
        // check the Accept header for version info
        $matches = $this->extractVersionFromAcceptHeader($request);

        // if no version info in the Accept: header, return with defaults
        if (! $matches) {
            return $this->fromDefaults($request);
        }

        $version         = $this->versionDefaults;
        $version['from'] = 'header';
        // overwrite defaults with matched values
        foreach ($this->versionDefaults as $key => $val) {
            $version[$key] = $matches[$key] ?? $version[$key] ?? $val;
        }

        // prepend the requested version to the path so it can be routed
        $newPath = '/v' . $version['version'] . $request->getUri()->getPath();
        // return the modified request
        return $request
            // set the rewritten path in the request obj
            ->withUri($request->getUri()->withPath($newPath))
            // add an attribute with the requested version
            ->withAttribute(self::class, $version);
    }

    public function fromDefaults(ServerRequestInterface $request): ServerRequestInterface
    {
        // prepend the default version to the path
        $newPath = '/v' . $this->versionDefaults['version']
            . $request->getUri()->getPath();

        // return the modified request
        return $request
            // set the rewritten path in the request obj
            // don't modify path when using default
            // ->withUri($request->getUri()->withPath($newPath))
            // add an attribute with the default version
            ->withAttribute(self::class, $this->versionDefaults);
    }

    private function hasVersionInPath(ServerRequestInterface $request): bool
    {
        return (bool) $this->extractVersionFromPath($request);
    }

    /**
     * @return array
     */
    private function extractVersionFromPath(ServerRequestInterface $request): array
    {
        // the first value that matches the regex is the winner
        foreach ($this->pathRegex as $r) {
            if (preg_match($r, $request->getUri()->getPath(), $matches)) {
                break;
            }
        }

        return $matches ?? [];
    }

    private function hasVersionInAcceptHeader(ServerRequestInterface $request): bool
    {
        return (bool) $this->extractVersionFromAcceptHeader($request);
    }

    /**
     * @return array
     */
    private function extractVersionFromAcceptHeader(ServerRequestInterface $request): array
    {
        if (! $request->hasHeader('accept')) {
            return [];
        }

        $accept = $request->getHeader('accept');
        $accept = array_shift($accept);

        $mediaRanges = explode(',', $accept);

        foreach ($mediaRanges as $a) {
            // the first value that matches the regex is the winner
            foreach ($this->headerRegex as $r) {
                if (preg_match($r, $a, $matches)) {
                    break 2;
                }
            }
        }

        return $matches ?? [];
    }
}
