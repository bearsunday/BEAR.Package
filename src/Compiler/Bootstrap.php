<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Throwable;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 */

final class Bootstrap
{
    /** @var string */
    private $appDir;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->appDir = $meta->appDir;
    }

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
        $injector =  Injector::getInstance($appName, $context, $this->appDir);
        $injector->getInstance(AppInterface::class);
        $injector->getInstance(HttpCacheInterface::class);
        $router = $injector->getInstance(RouterInterface::class);
        $request = $router->match($globals, $server);
        try {
            /** @psalm-suppress all */
            $resource = $injector->getInstance(ResourceInterface::class);
            $resource->{$request->method}->uri($request->path)($request->query);
        } catch (Throwable $e) {
            $injector->getInstance(TransferInterface::class);

            return 1;
        }

        // @codeCoverageIgnoreStart
        return 0;
    }
}
