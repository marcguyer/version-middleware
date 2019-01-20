# Versioning for PSR-7 apps

[![Build Status](https://secure.travis-ci.org/marcguyer/version-middleware.svg?branch=master)](https://secure.travis-ci.org/marcguyer/version-middleware)
[![Coverage Status](https://coveralls.io/repos/github/marcguyer/version-middleware/badge.svg?branch=master)](https://coveralls.io/github/marcguyer/version-middleware?branch=master)

Provides version detection, making versioned resource routing possible in PSR-7 applications.

## Installation

Install this library using composer:

```bash
$ composer require marcguyer/version-middleware
```

Composer will ask if you'd like to inject the ConfigProvider if you're using `zendframework/zend-component-installer`. Answer yes or config it by hand.

## Usage

### Config

See the [ConfigProvider](src/ConfigProvider.php) for config defaults. You may override using the `versioning` key. For example, the default version is `1`. You might release a new version and set the default version to `2`. Any clients not specifying a version via path or header will then be hitting version 2 resources.

### Add to pipeline

Wire this middleware into your pipeline before routing. An example using a Zend Expressive pipeline:

```php
...
$app->pipe(ServerUrlMiddleware::class);
...
$app->pipe(Psr7Versioning\VersionMiddleware::class);
...
$app->pipe(RouteMiddleware::class);
...
```

### Routing

Now, you can route based on the rewritten URI path. For example, in Expressive:

```php
$app->get('/api/v1/ping', Api\Handler\PingHandler::class, 'api.ping');
$app->get('/api/v2/ping', Api\V2\Handler\PingHandler::class, 'api.v2.ping');
```

### Namespaced version

Now, using the above routing example, assuming your v1 Ping is in namespace `Api\Handler`, you may set the namespace for v2 Ping to be `Api\V2\Handler` and extend the v1 handler. Any reference to services, models, other middleware will follow that namespace. Or, copy everything you want to be in the new version into a new namespace entirely.

## Contributing

### Docker Image

The `Dockerfile` in the repo can be used to create a lightweight image locally for running tests and other composer scripts:

```sh
docker build --tag [your_chosen_image_name] .
```

### Run tests

```sh
docker run --rm -it -v $(pwd):/app [your_chosen_image_name] composer test
```
