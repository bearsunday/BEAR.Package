<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;

/**
 * @psalm-import-type Globals from RouterInterface
 * @psalm-import-type Server from RouterInterface
 */
interface WebRouterInterface extends RouterInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Globals $globals
     * @param Server  $server
     */
    public function match(array $globals, array $server);
}
