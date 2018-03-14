<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\Page\Api;

use BEAR\Resource\ResourceObject;

class Website extends ResourceObject
{
    public function onGet($id)
    {
        $this->body = [
            'url' => "http:://example.org/{$id}"
        ];

        return $this;
    }
}
