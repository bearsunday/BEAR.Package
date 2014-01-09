<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\WebContext;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AuraWebModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('Ray\Di\ProviderInterface')
            ->annotatedWith('webContext')
            ->to('BEAR\Package\Module\WebContext\WebContextProvider')
            ->in(Scope::SINGLETON);
    }
}
