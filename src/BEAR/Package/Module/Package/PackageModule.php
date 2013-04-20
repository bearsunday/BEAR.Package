<?php
/**
 * @package    BEAR.Package
 * @subpackage Module
 */
namespace BEAR\Package\Module\Package;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;
use Ray\Di\Module\InjectorModule;

/**
 * Package module
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class PackageModule extends AbstractModule
{
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

        // Constants (can be injected @Named("constant_name"))
        $this->install(new SundayModule\Constant\NamedModule($this->config));
        $this->bind()->annotatedWith('package_dir')->toInstance($packageDir);

        // Provide module (BEAR.Sunday extension interfaces)
        $this->install(new ProvideModule\ApplicationLogger\ApplicationLoggerModule);
        $this->install(new ProvideModule\WebResponse\HttpFoundationModule);
        $this->install(new ProvideModule\ConsoleOutput\ConsoleOutputModule);
        $this->install(new ProvideModule\Router\MinRouterModule);
        $this->install(new ProvideModule\ResourceView\TemplateEngineRendererModule);
        $this->install(new ProvideModule\ResourceView\HalModule);
        $this->install(new ProvideModule\TemplateEngine\Smarty\SmartyModule);

        // Package module
        $this->install(new Package\Module\Database\Dbal\DbalModule($this));
        $this->install(new Package\Module\Log\ZfLogModule);
        $this->install(new Package\Module\ExceptionHandle\HandleModule);
        $this->install(new Package\Module\Aop\NamedArgsModule);

        // Framework core module
        $this->install(new SundayModule\Framework\FrameworkModule($this));
        // Injector module ('Injected injector' knows all bindings)
        $this->install(new InjectorModule($this));
    }
}
