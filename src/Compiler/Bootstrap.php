<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Package\Types;
use BEAR\Resource\Method;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 */

final class Bootstrap
{
    private string $appDir;

    public function __construct(
        AbstractAppMeta $meta,
        private InjectorInterface|null $injector = null,
    ) {
        $this->appDir = $meta->appDir;
    }

    /**
     * @param AppName $appName
     * @param Context $context
     * @param Globals $globals
     * @param Server  $server
     *
     * @return 0|1
     */
    public function __invoke(string $appName, string $context, array $globals, array $server): int
    {
        assert($this->appDir !== '');
        $injector = $this->injector ?? Injector::getInstance($appName, $context, $this->appDir);
        $httpCache = $injector->getInstance(HttpCacheInterface::class);
        assert($httpCache instanceof HttpCacheInterface);
        // Resolving it is not enough: the constants a real entry point reads on every request
        // (BEAR\QueryRepository\Header) only load when this runs.
        $httpCache->isNotModified($server);
        $router = $injector->getInstance(RouterInterface::class);
        assert($router instanceof RouterInterface);
        $request = $router->match($globals, $server);
        try {
            $resource = $injector->getInstance(ResourceInterface::class);
            assert($resource instanceof ResourceInterface);
            $resource->newRequest(Method::from($request->method), $request->path, $request->query)();
        } catch (Throwable) {
            $injector->getInstance(TransferInterface::class);

            return 1;
        }

        // @codeCoverageIgnoreStart
        return 0;
        // @codeCoverageIgnoreEnd
    }
}
