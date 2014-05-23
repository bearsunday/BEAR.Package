<?php
namespace BEAR\Package\Module\Package;

use BEAR\Package\Module as Package;
use BEAR\Package\Provide as Provide;
use BEAR\Sunday\Module as Sunday;

use Ray\Di\AbstractModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\Module\EmbedResourceModule;

class PackageModule extends AbstractModule
{
    /**
     * @var array config
     */
    private $config;

    /**
     * @var string Application class name
     */
    private $appClass;

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $appClass
     * @param string $context
     * @param array  $config
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
        $this->bind('')->annotatedWith('app_context')->toInstance($this->context);
        $this->config['package_dir'] = dirname(dirname(dirname(__DIR__)));

        // Sunday Module
        $this->install(new Sunday\Constant\NamedModule($this->config));
        $this->install(new Sunday\Cache\CacheModule);
        $this->install(new Sunday\Code\CachedAnnotationModule($this));

        // Package Module
        $this->install(new Package\Di\DiCompilerModule($this));
        $this->install(new Package\Di\DiModule($this));
        $this->install(new Package\Cache\CacheAspectModule($this));
        $this->install(new Package\ExceptionHandle\HandleModule);

        // Resource Module
        $this->install(new ResourceModule($this->config['app_name'], $this->config['resource_dir']));
//        $this->install(new EmbedResourceModule($this));

        // application
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to($this->appClass);

        // Provide module (BEAR.Sunday extension interfaces)
        $this->install(new Provide\WebResponse\HttpFoundationModule);
        $this->install(new Provide\ConsoleOutput\ConsoleOutputModule);
        $this->install(new Provide\Router\WebRouterModule);
        $this->install(new Provide\ResourceView\TemplateEngineRendererModule);
        $this->install(new Provide\ResourceView\HalModule);

        // Contextual Binding
        if ($this->context === 'test') {
            $this->install(new Package\Resource\NullCacheModule($this));
        }
        if ($this->context === 'dev') {
            $this->install(new Provide\ApplicationLogger\ApplicationLoggerModule);
            $this->install(new Package\Resource\DevResourceModule($this));
            $this->install(new Provide\ApplicationLogger\DevApplicationLoggerModule($this));
        }
        $this->install(new Package\Database\Dbal\DbalModule($this));
        if ($this->context === 'prod') {
            $this->install(new Package\Cache\CacheAspectModule($this));
        }
    }
}
