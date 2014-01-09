<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class TimeStamper implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        /** @noinspection PhpUndefinedFieldInspection */
        $object->time = date("Y-m-d H:i:s", time());
        $result = $invocation->proceed();

        return $result;
    }
}
