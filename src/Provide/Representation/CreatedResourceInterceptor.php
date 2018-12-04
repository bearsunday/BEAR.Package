<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class CreatedResourceInterceptor implements MethodInterceptor
{
    /**
     * @var CreatedResourceRenderer
     */
    private $renderer;

    public function __construct(CreatedResourceRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        /** @var ResourceObject $ro */
        $ro = $invocation->proceed();
        $isCreated = $ro->code === 201 && isset($ro->headers['Location']);
        if (! $isCreated) {
            return $ro;
        }
        $ro->setRenderer($this->renderer);

        return $ro;
    }
}
