<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Sunday\Extension\Aop;

use BEAR\Sunday\Extension\Aop\NamedArgsInterface;
use Ray\Aop\MethodInvocation;

/**
 * Interface for named parameter in interceptor
 *
 * @package    BEAR.Sunday
 * @subpackage Application
 */
class NamedArgs implements NamedArgsInterface
{
    public function get(MethodInvocation $invocation)
    {
    }
}
