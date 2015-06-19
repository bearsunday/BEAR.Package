<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Context;

use BEAR\Sunday\Annotation\DefaultSchemeHost;
use Ray\Di\AbstractModule;

class ApiModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith(DefaultSchemeHost::class)->toInstance('app://self');
    }
}
