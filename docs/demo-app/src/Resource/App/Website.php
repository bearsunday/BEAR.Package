<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\ResourceObject;

class Website extends ResourceObject
{
    public function onGet($id)
    {
        $this['url'] = "http:://example.org/{$id}";
        $this['id'] = $id;

        return $this;
    }
}
