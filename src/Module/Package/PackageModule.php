<?php
namespace BEAR\Package\Module\Package;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Module\Database\Dbal\DbalModule;
use BEAR\Package\Module\Resource\DevResourceModule;
use BEAR\Package\Module\Resource\NullCacheModule;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Package\Provide\ResourceView\HalModule;
use BEAR\Sunday\Module as SundayModule;
use BEAR\Sunday\Module\Constant\NamedModule;
use BEAR\Sunday\Module\Resource\ResourceCacheModule;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
use Ray\Di\Module\InjectorModule;
use BEAR\Package\Module\Cache\CacheModule;

class PackageModule extends AbstractModule
{
    private $config;

    /**
     * Application class name
     *
     * @var string
     */
    private $appClass;

    /**
     * @var string
     */
    private $context;

    /**
     * @param AbstractModule   $appClass
     * @param \Ray\Aop\Matcher $context
     * @param array            $config
     */
    public function __construct($appClass, $context, array $config)
    {
        $this->appClass = $appClass;
        $this->context = $context;
        $this->config = $config;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // application
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to($this->appClass);
        // config
        $this->config['package_dir'] = dirname(dirname(dirname(__DIR__)));
        $this->install(new NamedModule($this->config));

        if ($this->context === 'test') {
            $this->install(new NullCacheModule($this));
        }
        // Provide module (BEAR.Sunday extension interfaces)
        $this->install(new ProvideModule\WebResponse\HttpFoundationModule);
        $this->install(new ProvideModule\ConsoleOutput\ConsoleOutputModule);
        $this->install(new ProvideModule\Router\WebRouterModule);
        $this->install(new ProvideModule\ResourceView\TemplateEngineRendererModule);
        $this->install(new ProvideModule\ResourceView\HalModule);

        // Package module
        $this->install(new Package\Module\Log\ZfLogModule);
        $this->install(new Package\Module\ExceptionHandle\HandleModule);
        $this->install(new Package\Module\Aop\NamedArgsModule);

        // Framework core module
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        $this->install(new SundayModule\Resource\ResourceCacheModule);

        // Cache Module
        $this->install(new CacheModule($this));

        if ($this->context === 'dev') {
            $this->install(new ProvideModule\ApplicationLogger\ApplicationLoggerModule);
            $this->install(new DevResourceModule($this));
            $this->install(new ProvideModule\ApplicationLogger\DevApplicationLoggerModule($this));
        }
        $this->install(new DbalModule($this));

        // end of configuration in production
        if ($this->context === 'prod') {
            $this->install(new CacheModule($this));
        }

        if ($this->context === 'test') {
            $this->install(new NullCacheModule($this));
        }

        // install injector
        $this->bind('Ray\Di\InjectorInterface')->toInstance($this->dependencyInjector);
    }
}
