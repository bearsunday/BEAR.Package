<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

final class StubbedTxInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation): mixed
    {
        return 'tx(' . (string) $invocation->proceed() . ')';
    }
}
