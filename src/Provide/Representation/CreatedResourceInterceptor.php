<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ResourceObject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

use function assert;

class CreatedResourceInterceptor implements MethodInterceptor
{
    public function __construct(
        private CreatedResourceRenderer $renderer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->proceed();
        assert($ro instanceof ResourceObject);
        $isCreated = $ro->code === 201 && isset($ro->headers['Location']);
        if (! $isCreated) {
            return $ro;
        }

        $ro->setRenderer($this->renderer);

        return $ro;
    }
}
