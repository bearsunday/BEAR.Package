<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;

interface WebRouterInterface extends RouterInterface
{
    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server);
}
