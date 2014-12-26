<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\Package\Provide\Router\WebRouterModule;
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
        $this->bind('')->annotatedWith('app_name')->toInstance($this->appMeta->name);
        $this->install(new SundayModule);
        $this->override(new WebRouterModule);
        $this->bindResources();
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
