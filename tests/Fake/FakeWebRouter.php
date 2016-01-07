<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
