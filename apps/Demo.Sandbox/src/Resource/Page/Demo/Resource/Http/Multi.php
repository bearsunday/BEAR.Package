<?php

namespace Demo\Sandbox\Resource\Page\Demo\Resource\Http;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;

class Multi extends Page
{
    use ResourceInject;

    public $body = [
        'xml' => ''
    ];

    public function onGet()
    {
        $response = $this->resource
            ->get
            ->uri('http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss')
            ->sync
            ->request()

            ->get
            ->uri('http://phpspot.org/blog/index.xml')
            ->eager
            ->sync
            ->request()

            ->get
            ->uri('http://rss.excite.co.jp/rss/excite/odd')
            ->eager
            ->request();

        /** @var $response \BEAR\Resource\Adapter\Http\Guzzle */

        $this['xml'] = print_r($response->body, true);

        return $this;
    }
}
