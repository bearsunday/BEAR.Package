<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Ray\Di\AbstractModule;
use BEAR\Package\Module\Database\Dbal\Interceptor\TimeStamper;
use BEAR\Package\Module\Database\Dbal\Interceptor\Transactional;

/**
 * DBAL module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class DbalModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        // @Db
        $this->installDbInjector();
        // @Transactional
        $this->installTransaction();
        // @Time
        $this->installTimeStamper();
    }

    /**
     * @Db - db setter
     */
    private function installDbInjector()
    {
        $dbInjector = $this->requestInjection(__NAMESPACE__ . '\Interceptor\DbInjector');
        $this->bindInterceptor(
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Db'),
            $this->matcher->startWith('on'),
            [$dbInjector]
        );
    }

    /**
     * @Transactional - db transaction
     */
    private function installTransaction()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Transactional'),
            [new Transactional]
        );
    }

    /**
     * @Time - put time to 'time' property
     */
    private function installTimeStamper()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Time'),
            [new TimeStamper]
        );
    }
}
