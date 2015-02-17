<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\VndErrorModule;
use BEAR\Package\Provide\Router\AuraRouterModule;
use BEAR\Package\Provide\Router\WebRouterModule;
use BEAR\Package\Provide\Transfer\EtagResponseModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Module\SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class PackageModule extends AbstractModule
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->installAppModule();
        $this->install(new SundayModule);
        $this->install(new QueryRepositoryModule);
        $this->install(new EtagResponseModule);
        $this->install(new AuraRouterModule());
        $this->install(new WebRouterModule);
        $this->install(new VndErrorModule);
        $this->bindResources();
    }

    private function installAppModule()
    {
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $this->bind(AppInterface::class)->to($this->appMeta->name . '\Module\App');
        $this->bind('')->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
    }

    /**
     * Bind all resources in {appDir}/Resource directory as singleton
     */
    private function bindResources()
    {
        $list = $this->appMeta->getResourceListGenerator();
        foreach ($list as list($class,)) {
            $this->bind($class)->in(Scope::SINGLETON);
        }
    }
}
