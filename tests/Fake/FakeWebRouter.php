<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Provide\Router\WebRouter;

class FakeWebRouter extends WebRouter
{
    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        unset($name);
        if ((bool) $data === false) {
            return false;
        }

        return 'page://self/generated-uri';
    }
}
