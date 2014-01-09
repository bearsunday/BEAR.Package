<?php
/**
 * Time message
 */
namespace Demo\Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * +Time message add interceptor
 */
class TimeMessage implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $time = date('g:i');
        $result = $invocation->proceed() . ". It is {$time} now !";

        return $result;
    }
}
