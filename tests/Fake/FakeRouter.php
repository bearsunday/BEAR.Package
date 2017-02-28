<?php

namespace BEAR\Package;

use BEAR\Sunday\Extension\Router\RouterInterface;

class FakeRouter implements RouterInterface
{
    /**
     * {@inheritdoc}
     */
    public function match(array $globals, array $server)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $data)
    {
        unset($name);

        return '/task/' . $data['id'];
    }
}
