<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\Param;
use BEAR\Resource\SignalParameter;
use BEAR\Resource\ParamProviderInterface;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use BEAR\Resource\SignalParameterInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\InstanceInterface;
use Ray\Di\ProviderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class SignalParamProvider implements ProviderInterface
{
    /**
     * @var array [$varName => $parameterProvider]
     */
    private $paramProviders;

    /**
     * @var \BEAR\Resource\Exception\SignalParameter
     */
    private $signalParam;

    /**
     * @param AbstractModule $module
     * @param array          $config
     *
     * @Inject
     * @Named("paramProviders=param_providers")
     */
    public function __construct(InstanceInterface $injector, $paramProviders)
    {
        $this->signalParam = new SignalParameter(new Manager(new HandlerFactory, new ResultFactory, new ResultCollection), new Param);
        $this->paramProviders = unserialize($paramProviders);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        foreach($this->paramProviders as $varName => $paramProvider) {
            /** @var $paramProvider ParamProviderInterface */
            $this->signalParam->attachParamProvider($varName, $paramProvider);
        }

        return $this->signalParam;
    }
}
