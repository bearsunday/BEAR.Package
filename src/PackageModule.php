<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\Package\Provide\Error\VndError;
use BEAR\Package\Provide\Router\AuraRouterModule;
use BEAR\Package\Provide\Router\WebRouterModule;
use BEAR\Package\Provide\Transfer\EtagResponseModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Extension\Error\ErrorInterface;
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
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $this->bind(AppInterface::class)->to($this->appMeta->name . '\Module\App');
        $this->bind('')->annotatedWith('app_name')->toInstance($this->appMeta->name);
        $this->bind(ErrorInterface::class)->to(VndError::class);
        $this->install(new SundayModule);
        $this->bindResources();
        $this->install(new QueryRepositoryModule($this->appMeta->name));
        $this->install(new EtagResponseModule);
        $this->install(new AuraRouterModule());
        $this->install(new WebRouterModule);
    }

    /**
     * Bind all resources in {appDir}/Resource directory as singleton
     */
    private function bindResources()
    {
        $list = (new AppReflector($this->appMeta))->resourceList();
        foreach ($list as list($class,)) {
            $this->bind($class)->in(Scope::SINGLETON);
        }
    }
}
