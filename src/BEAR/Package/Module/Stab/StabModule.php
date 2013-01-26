<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Stab;

use Ray\Di\AbstractModule;

/**
 * Stab Module
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class StabModule extends AbstractModule
{
    /**
     * @var array
     */
    private $stab;

    public function __construct(array $stab)
    {
        $this->stab = $stab;
    }

    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        foreach ($this->stab as $class => $value) {
            $this->bindInterceptor(
                $this->matcher->subclassesOf($class),
                $this->matcher->any(),
                [new Stab($value)]
            );
        }

    }
}
