<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class ResourceGraphModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $resourceGraph = $this->requestInjection(__NAMESPACE__ . '\Interceptor\ResourceGraph');
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\ResourceGraph'),
            [$resourceGraph]
        );
    }
}
