<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Compiler\Module\App;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Application\AppInterface;
use Throwable;

use function assert;
use function dirname;

/**
 * @psalm-import-type Globals from \BEAR\Sunday\Extension\Router\RouterInterface
 * @psalm-import-type Server from \BEAR\Sunday\Extension\Router\RouterInterface
 */
final class Bootstrap
{
    /**
     * @psalm-param Globals $globals
     * @psalm-param Server  $server
     * @phpstan-param array<string, mixed> $globals
     * @phpstan-param array<string, mixed> $server
     *
     * @return 0|1
     */
    public function __invoke(string $context, array $globals, array $server): int
    {
        $tmpDir = dirname(__DIR__, 2) . '/tests/tmp';
        $app = Injector::getInstance('BEAR\Package\Compiler', $context, $tmpDir)->getInstance(AppInterface::class);
        assert($app instanceof App);
        if ($app->httpCache->isNotModified($server)) {
            $app->httpCache->transfer();

            return 0;
        }

        $request = $app->router->match($globals, $server);
        try {
            /** @psalm-suppress all */
            $response = $app->resource->{$request->method}->uri($request->path)($request->query);
            assert($response instanceof ResourceObject);
            $response->transfer($app->responder, $server);

            return 0;
        } catch (Throwable $e) {
            $app->throwableHandler->handle($e, $request)->transfer();

            return 1;
        }
    }
}
