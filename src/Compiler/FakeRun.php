<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector as PackageInjectorFacade;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\QueryRepository\EtagSetter;
use BEAR\QueryRepository\HttpCache;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Aop\InterceptTrait;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Aop\WeavedInterface;
use Ray\Di\InjectorInterface;

use function assert;
use function class_exists;
use function get_object_vars;
use function headers_sent;
use function interface_exists;
use function ob_end_clean;
use function ob_start;
use function trait_exists;

final class FakeRun
{
    public function __construct(
        private InjectorInterface $injector,
        private string $context,
        private AbstractAppMeta $appMeta,
    ) {
    }

    /**
     * @psalm-suppress MixedFunctionCall
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress MixedPropertyFetch
     */
    public function __invoke(): void
    {
        $bootstrap = new Bootstrap($this->appMeta, $this->injector);
        $_SERVER['HTTP_IF_NONE_MATCH'] = '0';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['argc'] = 3;
        $_SERVER['argv'] = ['', 'get', 'page:://self/'];
        /** @psalm-suppress ArgumentTypeCoercion, InvalidArgument */
        ($bootstrap)($this->appMeta->name, $this->context, $GLOBALS, $_SERVER); // @phpstan-ignore-line
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $app = $this->injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);
        $appVars = get_object_vars($app);
        $resource = $appVars['resource'] ?? null;
        assert($resource instanceof ResourceInterface);
        $ro = $this->injector->getInstance(NullPage::class);
        $ro->uri = new Uri('app://self/');
        $response = $resource->object($ro)(['required' => 'string']);
        $this->transfer($response);

        // Linked at the end of preload, not autoloaded: an AOP proxy whose interface or trait
        // is missing is dropped with a warning at every startup.
        interface_exists(HttpCacheInterface::class);
        interface_exists(WeavedInterface::class);
        trait_exists(InterceptTrait::class);
        class_exists(HttpCache::class);
        class_exists(EtagSetter::class);
        class_exists(ReflectiveMethodInvocation::class);
        class_exists(PackageInjectorFacade::class);
    }

    /**
     * Run the response through the real responder, so preload records it and Output.
     *
     * Buffered, because the body would otherwise land in the compile report - and skipped once
     * anything has been printed, since header() then warns instead of being the CLI no-op it is
     * in the preload worker, which prints nothing before this point.
     */
    private function transfer(ResourceObject $response): void
    {
        if (headers_sent()) {
            return;
        }

        $transfer = $this->injector->getInstance(TransferInterface::class);
        assert($transfer instanceof TransferInterface);
        ob_start();
        try {
            /** @var array<string, mixed> $server */
            $server = $_SERVER;
            $response->transfer($transfer, $server);
        } finally {
            ob_end_clean();
        }
    }
}
