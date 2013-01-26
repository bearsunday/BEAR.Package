<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Stab;

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
class Stab implements MethodInterceptor
{
    use LogInject;

    /**
     * Stab data
     *
     * @var array
     */
    private $stab;

    /**
     * Constructor
     *
     * @param mixed $stab
     */
    public function __construct(array $stab)
    {
        $this->stab = $stab;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Aop.MethodInterceptor::invoke()
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        if (is_array($object->body)) {
            $object->body = array_merge($object->body, $this->stab);
        } else {
            $object->body = $this->stab;
        }
        return $object;
    }
}
