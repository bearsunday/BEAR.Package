<?php
/**
 * @package    Skeleton
 * @subpackage Module
 */
namespace Skeleton\Module\App;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;
use BEAR\Package\Provide as ProvideModule;
use Ray\Di\AbstractModule;

/**
 * Application module
 *
 * @package    Skeleton
 * @subpackage Module
 */
class AppModule extends AbstractModule
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        // install package module
        $this->install(new SundayModule\Constant\NamedModule($this->config));

        $scheme = __NAMESPACE__ . '\SchemeCollectionProvider';
        $this->install(new PackageModule\PackageModule($this, $scheme));

        // install twig
//        $this->install(new ProvideModule\TemplateEngine\Twig\TwigModule($this));

        // dependency binding for application
        $this->bind('BEAR\Sunday\Extension\Application\AppInterface')->to('Skeleton\App');
    }
}
