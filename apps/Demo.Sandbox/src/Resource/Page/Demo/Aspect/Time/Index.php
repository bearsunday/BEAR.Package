<?php

namespace Demo\Sandbox\Resource\Page\Demo\Aspect\Time;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Time;

/**
 * Cache page
 */
class Index extends Page
{
    /**
     * @var string
     */
    public $time;

    public $body = [
        'time' => ''
    ];

    protected $smarty;

    /**
     * @Time
     */
    public function onGet()
    {
        // $this->time is refreshed as current time on each method call with @Time
        $this['time'] = $this->time;

        return $this;
    }
}
