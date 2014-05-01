<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Stub;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class Stub implements MethodInterceptor
{
    /**
     * Stub data
     *
     * @var array
     */
    private $stub;

    /**
     * @param mixed $stub
     */
    public function __construct(array $stub)
    {
        $this->stub = $stub;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        if (is_array($object->body)) {
            $object->body = array_merge($object->body, $this->stub);
            return $object;
        }
        $object->body = $this->stub;
        return $object;
    }
}
