<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Stub;

use BEAR\Sunday\Inject\LogInject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;

/**
 * Cache interceptor interface
 *
 * @package    BEAR.Sunday
 * @subpackage Interceptor
 */
class Stub implements MethodInterceptor
{
    use LogInject;

    /**
     * Stub data
     *
     * @var array
     */
    private $stub;

    /**
     * Constructor
     *
     * @param mixed $stub
     */
    public function __construct(array $stub)
    {
        $this->stub = $stub;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        if (is_array($object->body)) {
            $object->body = array_merge($object->body, $this->stub);
        } else {
            $object->body = $this->stub;
        }
        return $object;
    }
}
