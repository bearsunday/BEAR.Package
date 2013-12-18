<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use BEAR\Package\Provide as ProvideModule;
use BEAR\Resource\ParamProviderInterface;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

/**
 * Signal parameter module
 */
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

    protected function configure()
    {
        $signalParam = $this->requestInjection('BEAR\Resource\SignalParameterInterface');
        /* @var $signalParam \BEAR\Resource\SignalParameterInterface */
        foreach ($this->config as $varName => $provider) {
            $paramProvider = $this->requestInjection($provider);
            /** @var $paramProvider ParamProviderInterface */
            $signalParam->attachParamProvider($varName, $paramProvider);
        }
        $this->bind('BEAR\Resource\SignalParameterInterface')->toInstance($signalParam);
    }
}
