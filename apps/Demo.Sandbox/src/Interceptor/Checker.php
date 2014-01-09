<?php

namespace Demo\Sandbox\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Check env interceptor
 */
class Checker implements MethodInterceptor
{
    /**
     * Tmp dir
     *
     * @var string
     */
    private $tmpDir;

    /**
     * Constructor
     *
     * @param string $tmpDir
     *
     * @Inject
     * @Named("tmp_dir")
     */
    public function __construct($tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        if (is_writable($this->tmpDir)) {
            return $invocation->proceed();
        }
        $pageObject = $invocation->getThis();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $tmpDir = $this->tmpDir;
        $pageObject->view = include __DIR__ . '/Checker/error.php';

        return $pageObject;
    }
}
