<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Stub;

use Ray\Di\AbstractModule;

/**
 * StubModule
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class StubModule extends AbstractModule
{
    /**
     * @var array
     */
    private $stub;

    /**
     * @param array $stub
     */
    public function __construct(array $stub)
    {
        parent::__construct();
        $this->stub = $stub;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        foreach ($this->stub as $class => $value) {
            $this->bindInterceptor(
                $this->matcher->subclassesOf($class),
                $this->matcher->any(),
                [new Stub($value)]
            );
        }

    }
}
