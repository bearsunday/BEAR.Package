<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Di;

use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;

class DiProvider implements ProviderInterface
{
    private $module;

    public function __construct(AbstractModule $module)
    {
        $this->module = $module;
    }

    public function get()
    {
        return Injector::create([$this->module]);
    }
}
