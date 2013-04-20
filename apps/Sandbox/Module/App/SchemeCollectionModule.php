<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module\Common;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Adapter\App as AppAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use BEAR\Sunday\Inject\AppNameInject;
use BEAR\Sunday\Inject\InjectorInject;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface as Provide;
use Ray\Di\Di\Inject;

/**
 * Scheme collection
 *
 * @package    Sandbox
 * @subpackage Module
 */
class SchemeCollectionModule extends AbstractModule
{
    use AppNameInject;
    use InjectorInject;

    /**
     * @var SchemeCollectionInterface
     */
    private $schemeCollection;

    /**
     * @param SchemeCollectionInterface $schemeCollection
     *
     * @Inject
     */
    public function setSchemeCollection(SchemeCollectionInterface $schemeCollection, InjectorInterface $injector)
    {
        $this->schemeCollection = $schemeCollection;
        $this->injector = $injector;
        $this->configure();
    }

    /**
     * Return resource adapter set.
     *
     * @return SchemeCollection
     */
    protected function configure()
    {
        $schemeCollection = $this->schemeCollection;
        $pageAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\Page');
        $appAdapter = new AppAdapter($this->injector, $this->appName, 'Resource\App');
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);
    }
}
