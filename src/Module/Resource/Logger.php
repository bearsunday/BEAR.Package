<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use BEAR\Sunday\Inject\LogInject;
use BEAR\Sunday\Inject\PsrLoggerInject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class Logger implements MethodInterceptor
{
    use PsrLoggerInject;

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $result = $invocation->proceed();
        $class = get_class($invocation->getThis());
        $object = $invocation->getThis();
        $args = $invocation->getArguments();
        $object->headers['x-args'] = json_encode($args);
        $input = substr(json_encode($args), 0, 80);
        $output = substr(json_encode($result), 0, 80);
        $log = "target = [{$class}], input = [{$input}], result = [{$output}]";
        $this->logger->info($log);

        return $result;
    }
}
