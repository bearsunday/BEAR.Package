<?php

namespace Sandbox\Resource\Page\Demo\Aspect;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Cache;
use Ray\Di\Di\Inject;
use Smarty;
/**
 * Cache page
 */
class Cachepage extends Page
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
        $this['num'] = rand(1,100);

        return $this;
    }
}
