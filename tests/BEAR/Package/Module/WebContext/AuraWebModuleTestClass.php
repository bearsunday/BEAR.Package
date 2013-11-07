<?php

namespace BEAR\Package\Module\WebContext;

use Ray\Di\ProviderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class AuraWebModuleTestClass
{
    public $webContextProvider;

    /**
     * @Inject
     * @Named("webContext")
     */
    public function setWebContextProvider(ProviderInterface $provider)
    {
        $this->webContextProvider = $provider;
    }
}
