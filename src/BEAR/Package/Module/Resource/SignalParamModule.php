<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use BEAR\Package\Provide as ProvideModule;
use BEAR\Resource\Param;
use BEAR\Resource\ParamProviderInterface;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

/**
 * Signal parameter module
 *
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
        $signalParam = $this->requestInjection('BEAR\Resource\SignalParamsInterface');
        /* @var $signalParam \BEAR\Resource\SignalParamsInterface */
        foreach ($this->config as $varName => $varProvider) {
            $signalParam->attachParamProvider($varName, $this->requestInjection($varProvider));
        }
        $this->bind('BEAR\Resource\SignalParamsInterface')->toInstance($signalParam);
    }
}
