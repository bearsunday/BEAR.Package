<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Constant;

use BEAR\Sunday\Module\Constant\NamedModule;
use Ray\Di\AbstractModule;

/**
 * DBAL module
 */
class ConfigModule extends AbstractModule
{
    /**
     * Constants
     *
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $params = [];

    public function __construct($context, $confDir)
    {
        $this->config = (require "{$confDir}/{$context}.php") + (require "{$confDir}/prod.php");
        $this->params = (require "{$confDir}/params/{$context}.php") + (require "{$confDir}/params/prod.php");
        parent::__construct();
    }

    protected function configure()
    {
        $this->install(new NamedModule($this->config));
    }
}