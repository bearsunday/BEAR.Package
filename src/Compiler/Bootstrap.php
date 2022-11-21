<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Throwable;

use function assert;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 */

final class Bootstrap
{
    private string $appDir;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->appDir = $meta->appDir;
    }

    /**
     * @param Globals $globals
     * @param Server  $server
     *
     * @return 0|1
     */
    public function __invoke(string $appName, string $context, array $globals, array $server): int
    {
        $injector =  Injector::getInstance($appName, $context, $this->appDir);
        $injector->getInstance(HttpCacheInterface::class);
        $router = $injector->getInstance(RouterInterface::class);
        assert($router instanceof RouterInterface);
        $request = $router->match($globals, $server);
        try {
            /** @psalm-suppress all */
            $resource = $injector->getInstance(ResourceInterface::class);
            $resource->{$request->method}->uri($request->path)($request->query);
        } catch (Throwable) {
            $injector->getInstance(TransferInterface::class);

            return 1;
        }

        // @codeCoverageIgnoreStart
        return 0;
    }
}
