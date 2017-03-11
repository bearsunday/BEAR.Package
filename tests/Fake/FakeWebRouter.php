<?php
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
        if ($data === false) {
            return false;
        }

        return 'page://self/generated-uri';
    }
}
