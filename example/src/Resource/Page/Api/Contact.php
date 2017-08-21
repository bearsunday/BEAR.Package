<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\Page\Api;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Inject\ResourceInject;

class Contact extends ResourceObject
{
    use ResourceInject;

    public function onGet($id)
    {
        $this['contact'] = $this->resource->get->uri('app://self/user/friend')->withQuery(['id' => $id])->eager->request();

        return $this;
    }
}
