<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

class FakeErrorRouter implements RouterInterface
{
    /**
     * @inheritDoc
     */
    public function generate($name, $data)
    {
        throw new \RuntimeException();
    }

    /**
     * @inheritDoc
     */
    public function match(array $globals, array $server)
    {
        throw new \RuntimeException();
    }
}
