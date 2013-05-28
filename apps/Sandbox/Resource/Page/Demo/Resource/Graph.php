<?php

namespace Sandbox\Resource\Page\Demo\Resource;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Cache;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Annotation\ResourceGraph;

/**
 * Resource graph annotation
 */
class Graph extends Page
{
    use ResourceInject;

    public $body = [
        'greeting' => 'app://self/first/greeting',
        'performance' => 'app://self/performance'
    ];

    /**
     * @param string $name
     *
     * @Cache(5)
     * @ResourceGraph
     */
    public function onGet($name = 'Resource Graph')
    {
        $this['greeting'] = $this->body['greeting']->withQuery(['name' => $name]);

        return $this;
    }
}
