<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal\Interceptor;

use Exception;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use ReflectionProperty;

/**
 * Db transaction interceptor
 */
class Transactional implements MethodInterceptor
{
    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $object = $invocation->getThis();
        $ref = new ReflectionProperty($object, 'db');
        $ref->setAccessible(true);
        $db = $ref->getValue($object);
        $db->beginTransaction();
        try {
            $invocation->proceed();
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}
