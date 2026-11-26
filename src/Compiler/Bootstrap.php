<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Package\Types;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Method;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;
use function explode;
use function file_put_contents;
use function sprintf;

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

    /** @param string|null $failureLog where a failed request is reported; null reports nothing */
    public function __construct(
        AbstractAppMeta $meta,
        private InjectorInterface|null $injector = null,
        private string|null $failureLog = null,
    ) {
        $this->appDir = $meta->appDir;
    }

    /**
     * Run the request a deployed entry point runs, and hand back what it produced.
     *
     * The caller decides whether to transfer it: rendering is where a response format loads its
     * renderer, and a preload that never rendered describes no format at all.
     *
     * @param AppName $appName
     * @param Context $context
     * @param Globals $globals
     * @param Server  $server
     *
     * @return ResourceObject|null the response, or null when the request ended in the error path
     */
    public function __invoke(string $appName, string $context, array $globals, array $server): ResourceObject|null
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

            return $resource->newRequest(Method::from($request->method), $request->path, $request->query)();
        } catch (ResourceNotFoundException) {
            // An application with nothing at the path has nothing to record there.
            $injector->getInstance(TransferInterface::class);

            return null;
        } catch (Throwable $e) {
            if ($this->failureLog !== null) {
                file_put_contents($this->failureLog, sprintf("%s %s failed while recording preload.php: %s: %s\n", $request->method, $request->path, $e::class, explode("\n", $e->getMessage())[0]));
            }

            $injector->getInstance(TransferInterface::class);

            return null;
        }
    }
}
