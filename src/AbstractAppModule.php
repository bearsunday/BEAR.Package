<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Application\AbstractApp;
use Ray\Di\AbstractModule;

class AbstractAppModule extends AbstractModule
{
    /**
     * @var AbstractAppMeta
     */
    private $app;

    public function __construct(AbstractAppMeta $app)
    {
        $this->app = $app->name . '\Module\App';
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // bind default app class name
        $this->bind(AbstractApp::class)->to($this->app);
    }
}
