<?php

namespace BEAR\Package\Module\Session\AuraSession;

use Ray\Di\ProviderInterface;
use Aura\Session\Manager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;
use Aura\Session\Randval;
use Aura\Session\Phpfunc;

class SessionProvider implements ProviderInterface
{
    public function get()
    {
        return new Manager(
            new SegmentFactory,
            new CsrfTokenFactory(
                new Randval(
                    new Phpfunc
                )
            ),
            $_COOKIE
        );
    }
}
