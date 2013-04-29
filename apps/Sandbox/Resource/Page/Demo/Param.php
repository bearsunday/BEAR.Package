<?php

namespace Sandbox\Resource\Page\Demo;

use BEAR\Resource\AbstractObject as Page;

class Param extends Page
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
