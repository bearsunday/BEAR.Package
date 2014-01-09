<?php

namespace Demo\Sandbox\Resource\Page\Demo\Resource\Http;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;

class Index extends Page
{
    use ResourceInject;

    public $body = [
        'news' => ''
    ];

    public function onGet()
    {
        $xml = $this->resource
            ->get
            ->uri('http://www.feedforall.com/sample.xml')
            ->eager
            ->request()
            ->body;

        /** @var $xml \SimpleXMLElement */
        $this['xml'] = print_r($xml, true);

        return $this;
    }
}
