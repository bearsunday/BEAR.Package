<?php

namespace Sandbox\Module\App;

use BEAR\Package\Module\Form\AuraForm\AuraFormModule;
use BEAR\Package\Module\Package\PackageModule;
use BEAR\Package\Module\Resource\ResourceGraphModule;
use BEAR\Package\Module\Resource\SignalParamModule;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;
use Sandbox\Interceptor\TimeMessage;

/**
 * Application module
 */
class AppModule extends AbstractModule
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $params;

    /**
     * @param string $mode
     *
     * @throws \LogicException
     */
    public function __construct($mode)
    {
        $appDir = dirname(dirname(__DIR__));
        $modeConfig = $appDir . "/config/{$mode}.php";
        if (!file_exists($modeConfig)) {
            throw new \LogicException("Invalid mode {$mode}");
        }
        $this->config = (require $modeConfig) + (require $appDir . '/config/prod.php');
        // signal parameter
        $paramConfig = $appDir . "/Params/config/{$mode}.php";
        $this->params = (require $paramConfig) + (require $appDir . '/Params/config/prod.php');
        parent::__construct();
    }

    protected function configure()
    {
        // install package module
        $this->install(new PackageModule($this->config));

        // install twig
        // $this->install(new ProvideModule\TemplateEngine\Twig\TwigModule($this));

        // install optional package
        $this->install(new SignalParamModule($this, $this->params));
        $this->install(new AuraFormModule);

        // dependency binding for application
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to('Sandbox\App');
        $this->bind()->annotatedWith('greeting_msg')->toInstance('Hola');
        $this->bind('BEAR\Resource\RenderInterface')->annotatedWith('hal')->to(
            'BEAR\Package\Provide\ResourceView\HalRenderer'
        )->in(Scope::SINGLETON);
    }
}
