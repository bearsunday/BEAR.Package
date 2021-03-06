<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\NullPage;
use BEAR\QueryRepository\EtagSetter;
use BEAR\QueryRepository\HttpCache;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Provide\Transfer\HttpResponder;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\InjectorInterface;

use function assert;
use function class_exists;
use function property_exists;

class FakeRun
{
    /** @var InjectorInterface */
    private $injector;

    /** @var string */
    private $context;

    /** @var AbstractAppMeta  */
    private $appMeta;

    public function __construct(InjectorInterface $injector, string $context, AbstractAppMeta $appMeta)
    {
        $this->injector = $injector;
        $this->context = $context;
        $this->appMeta = $appMeta;
    }

    /**
     * @psalm-suppress MixedFunctionCall
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress MixedPropertyFetch
     */
    public function __invoke(): void
    {
        $appBootstrap = $this->appMeta->name . '\Bootstrap';
        $bootstrap = class_exists($appBootstrap) ? $appBootstrap : Bootstrap::class;
        $_SERVER['HTTP_IF_NONE_MATCH'] = '0';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        (new $bootstrap())($this->context, $GLOBALS, $_SERVER); // 200 OK
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        (new $bootstrap())($this->context, $GLOBALS, $_SERVER); // 405 MethodNotAllowedException
        $app = $this->injector->getInstance(AppInterface::class);
        assert(property_exists($app, 'resource'));
        assert(property_exists($app, 'responder'));
        $ro = $this->injector->getInstance(NullPage::class);
        $ro->uri = new Uri('app://self/');
        $ro = $app->resource->get->object($ro)(['required' => 'string']);
        assert($ro instanceof ResourceObject);
        $ro->transfer($app->responder, []);
        class_exists(HttpCacheInterface::class);
        class_exists(HttpCache::class);
        class_exists(HttpResponder::class);
        class_exists(EtagSetter::class);
        class_exists(ReflectiveMethodInvocation::class);
    }
}
