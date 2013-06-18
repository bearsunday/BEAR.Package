<?php

namespace Sandbox\Module;

use BEAR\Package\Module\Form\AuraForm\AuraFormModule;
use BEAR\Package\Module\Package\PackageModule;
use BEAR\Package\Module\Resource\NullCacheModule;
use BEAR\Package\Module\Resource\ResourceGraphModule;
use BEAR\Package\Module\Resource\SignalParamModule;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Package\Provide\ResourceView;
use BEAR\Package\Provide\ResourceView\HalModule;
use BEAR\Sunday\Module as SundayModule;
use BEAR\Sunday\Module\Constant\NamedModule as Constant;
use BEAR\Sunday\Module\Cqrs\CacheModule;
use BEAR\Sunday\Module\Resource\ApcModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;
use Sandbox\Module\DevModule;

/**
 * Application module
 */
class AppModule extends AbstractModule
{
    /**
     * Constants
     *
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $mode;

    /**
     * @param string $mode
     *
     * @throws \LogicException
     */
    public function __construct($mode = 'prod')
    {
        $dir = dirname(__DIR__);
        $this->mode = $mode = strtolower($mode);
        $this->config = (require "{$dir}/config/{$mode}.php") + (require "{$dir}/config/prod.php");
        $this->params = (require "{$dir}/Params/config/{$mode}.php") + (require "{$dir}/Params/config/prod.php");
        parent::__construct();
    }

    protected function configure()
    {
        // install constants
        $this->install(new Constant($this->config));

        // install core package
        $this->install(new PackageModule($this, 'Sandbox\App', $this->mode));

        //$this->install(new TwigModule($this));

        // install optional package
        $this->install(new SignalParamModule($this, $this->params));
        $this->install(new AuraFormModule);

        // end of configuration in production
        if ($this->mode === 'prod') {
            $this->install(new ApcModule($this));
        }

        if ($this->mode === 'test') {
            $this->install(new NullCacheModule($this));
        }

        if ($this->mode === 'api') {
            // install api output view package
            $this->install(new HalModule($this));
            //$this->install(new JsonModule($this));
        }

        if ($this->mode === 'dev') {
            $smarty = $this->requestInjection('Smarty');
            /** @var $smarty \Smarty */
            $smarty->force_compile = true;
        }

        if ($this->mode === 'stub') {
            //$this->install(new PackageStubModule($stub));
        }
        $this->install(new ResourceGraphModule($this));
        $this->install(new CacheModule($this));

        // application bindings
        $this->bind()->annotatedWith('greeting_msg')->toInstance('Hola');
        $this->bind('BEAR\Resource\RenderInterface')->annotatedWith('hal')->to(
            'BEAR\Package\Provide\ResourceView\HalRenderer'
        )->in(Scope::SINGLETON);

        // application aspect
        $this->install(new AopModule($this));
    }
}
