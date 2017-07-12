<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function onGet($name = 'BEAR.Sunday')
    {
        $this['greeting'] = 'Hello ' . $name;

        return $this;
    }
}
