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
use Ray\Di\Injector;

class SignalParamModule extends AbstractModule
{
    /**
     * @var array [$varName =>
     */
    private $config;

    /**
     * @param AbstractModule $module
     * @param array          $config
     */
    public function __construct(AbstractModule $module, array $config)
    {
        $this->config = $config;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $paramProviders = [];
        foreach ($this->config as $varName => $provider) {
            $paramProviders[$varName] = $this->requestInjection($provider);
        }

        $this->bind()->annotatedWith('param_providers')->toInstance(serialize($paramProviders));
        $this->bind('BEAR\Resource\SignalParameterInterface')->toProvider(__NAMESPACE__ . '\SignalParamProvider');
    }
}
