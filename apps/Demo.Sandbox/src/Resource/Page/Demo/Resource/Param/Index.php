<?php

namespace Demo\Sandbox\Resource\Page\Demo\Resource\Param;

use BEAR\Resource\ResourceObject as Page;

class Index extends Page
{
    public $body = [
        'now' => 'n/a'
    ];

    /**
     * @param $now
     *
     * @return $this
     */
    public function onGet($now)
    {
        $this->body['now'] = $now;

        return $this;
    }
}
