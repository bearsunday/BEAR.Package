<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\QueryRepository\EtagSetter;
use BEAR\QueryRepository\HttpCache;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\InjectorInterface;

use function assert;
use function class_exists;
use function get_object_vars;

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
        $resource->object($ro)(['required' => 'string']);
        class_exists(HttpCacheInterface::class);
        class_exists(HttpCache::class);
        class_exists(HttpResponder::class);
        class_exists(EtagSetter::class);
        class_exists(ReflectiveMethodInvocation::class);
    }
}
