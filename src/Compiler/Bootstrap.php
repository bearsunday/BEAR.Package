<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;
use Throwable;

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
    public function __invoke(string $appName, string $context, array $globals, array $server): int
    {
        $tmpDir = dirname(__DIR__, 2) . '/tests/tmp';
        $app = Injector::getInstance($appName, $context, $tmpDir)->getInstance(AppInterface::class);
        $app->httpCache->isNotModified($server);

        $request = $app->router->match($globals, $server);
        try {
            /** @psalm-suppress all */
            $app->resource->{$request->method}->uri($request->path)($request->query);
        } catch (Throwable $e) {
            $app->throwableHandler->handle($e, $request)->transfer();

            return 1;
        }

        // @codeCoverageIgnoreStart
        return 0;
    }
}
