<?php

namespace Demo\Sandbox\Resource\Page\Demo\Aspect\Cache;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Cache;
use Ray\Di\Di\Inject;
use Smarty;

/**
 * Cache page
 */
class Index extends Page
{
    public $body = [
        'num' => ''
    ];

    protected $smarty;

    /**
     * @Cache(3)
     */
    public function onGet()
    {
        $this['num'] = rand(1, 100);

        return $this;
    }
}
