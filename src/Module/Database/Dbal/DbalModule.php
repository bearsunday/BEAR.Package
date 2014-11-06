<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Database\Dbal;

use Ray\Di\AbstractModule;

class DbalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
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
        $this->bindInterceptor(
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Db'),
            $this->matcher->startsWith('on'),
            [__NAMESPACE__ . '\Interceptor\DbInjector']
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
            ['BEAR\Package\Module\Database\Dbal\Interceptor\Transactional']
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
            ['BEAR\Package\Module\Database\Dbal\Interceptor\TimeStamper']
        );
    }
}
