<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

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

        if (isset($data['id'])) {
            return '/task/' . $data['id'];
        }

        return '/task';
    }
}
