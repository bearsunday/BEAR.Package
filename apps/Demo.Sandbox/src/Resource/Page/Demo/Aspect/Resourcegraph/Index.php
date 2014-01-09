<?php

namespace Demo\Sandbox\Resource\Page\Demo\Aspect\Resourcegraph;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Annotation\Cache;
use Ray\Di\Di\Inject;
use BEAR\Sunday\Annotation\ResourceGraph;

/**
 * Resource graph annotation
 */
class Index extends Page
{
    use ResourceInject;

    public $body = [
        'greeting' => 'app://self/first/greeting',
        'performance' => 'app://self/performance'
    ];

    /**
     * @param string $name
     *
     * @ResourceGraph
     */
    public function onGet($name = 'Resource Graph')
    {
        // add query for app://self/first/greeting resource
        $this['greeting'] = $this->body['greeting']->withQuery(['name' => $name]);

        return $this;
    }
}
