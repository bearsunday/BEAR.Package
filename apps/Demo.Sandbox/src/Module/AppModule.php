<?php

namespace Demo\Sandbox\Module;

use BEAR\Package\Module\Di\DiModule;
use BEAR\Package\Module\Form\AuraForm\AuraFormModule;
use BEAR\Package\Module\Package\PackageModule;
use BEAR\Package\Module\Resource\ResourceGraphModule;
use BEAR\Package\Module\Resource\SignalParamModule;
use BEAR\Package\Module\Stub\StubModule;
use BEAR\Package\Provide\ResourceView;
use BEAR\Package\Provide\ResourceView\HalModule;
use BEAR\Sunday\Module as SundayModule;
use Demo\Sandbox\Module;
use Ray\Di\AbstractModule;
use BEAR\Package\Provide\TemplateEngine\Smarty\SmartyModule;
use BEAR\Package\Provide\TemplateEngine\Twig\TwigModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Di\Scope;
use BEAR\Package\Module\Di\DiCompilerModule;

class AppModule extends AbstractModule
{
    /**
     * Constants
     *
     * @var array
     */
    private $constants;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $context;

    private $appDir;

    /**
     * @param string $context
     *
     * @throws \LogicException
     *
     * @Inject
     * @Named("app_context")
     */
    public function __construct($context = 'prod')
    {
        $appDir = dirname(dirname(__DIR__));
        $this->context = $context;
        $this->appDir = $appDir;
        $this->constants = (require "{$appDir}/var/conf/{$context}.php") + (require "{$appDir}/var/conf/prod.php");
        $this->params = (require "{$appDir}/var/lib/params/{$context}.php") + (require "{$appDir}/var/lib/params/prod.php");
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule('Demo\Sandbox\App', $this->context, $this->constants));

        // install view package
        $this->install(new SmartyModule($this));
//        $this->install(new TwigModule($this));
//        $this->install(new AuraViewModule($this));

        // install optional package
        $this->install(new SignalParamModule($this, $this->params));
        $this->install(new AuraFormModule);

        // install develop module
        if ($this->context === 'dev') {
            $this->install(new App\Aspect\DevModule($this));
        }

        // install API module
        if ($this->context === 'api') {
            $this->install(new HalModule($this));
            //$this->install(new JsonModule($this));
        }

        // install application dependency
        $this->install(new App\Dependency);

        // install application aspect
        $this->install(new App\Aspect($this));

        if ($this->context === 'stub') {
            // install stub data
            $this->install(new StubModule(require "{$this->appDir}/var/lib/stub/resource.php"));
        }
    }
}
