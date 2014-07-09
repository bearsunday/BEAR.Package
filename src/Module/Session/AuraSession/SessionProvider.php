<?php

namespace BEAR\Package\Module\Session\AuraSession;

use Ray\Di\ProviderInterface;

class SessionProvider implements ProviderInterface
{
    public function get()
    {
        return new \Aura\Session\Manager(
            new \Aura\Session\SegmentFactory,
            new \Aura\Session\CsrfTokenFactory(
                new \Aura\Session\Randval(
                    new \Aura\Session\Phpfunc
                )
            ),
            $_COOKIE
        );
    }
}
