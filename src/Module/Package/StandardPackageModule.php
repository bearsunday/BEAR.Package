<?php
namespace BEAR\Package\Module\Package;

use BEAR\Package;
use BEAR\Package\Module;
use BEAR\Package\Provide as ProvideModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use BEAR\Package\Module\Resource\SignalParamModule;
use BEAR\Package\Module\Form\AuraForm\AuraFormModule;
use BEAR\Package\Provide\TemplateEngine\Twig\TwigModule;
use BEAR\Package\Provide\ResourceView\HalModule;

final class StandardPackageModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appDir;

    /**
     * @var string
     */
    private $context;

    public function __construct($appDir, $context, AbstractModule $module = null)
    {
        $this->appDir = $appDir;
        $this->context = $context;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $constantsCollection = require $this->appDir . '/var/conf/constants.php';
        $constants = $constantsCollection[$this->context] + $constantsCollection['prod'];
        $paramsCollection = require $this->appDir . '/var/conf/params.php';
        $params = $paramsCollection[$this->context] + $paramsCollection['prod'];
        $appClass = $constants['app_class'];

        $this->install(new PackageModule($appClass, $this->context, $constants));
        $this->install(new SignalParamModule($this, $params));
        $this->install(new TwigModule($this));
        $this->install(new AuraFormModule);
        // install API module
        if ($this->context === 'api') {
            $this->install(new HalModule($this));
            //$this->install(new JsonModule($this));
        }

    }
}
