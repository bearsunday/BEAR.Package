<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\InjectorInterface;

class CreatedResourceInterceptor implements MethodInterceptor
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var RouterInterface
     */
    private $router;

    private $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->proceed();
        $isCreated = $ro->code === 201 && $ro->uri->method === 'post' && isset($ro->headers['Location']);
        if (! $isCreated) {
            return $ro;
        }
        $renderer = $this->injector->getInstance(CreatedResourceRenderer::class);
        $ro->setRenderer($renderer);

        return $ro;
    }
}
