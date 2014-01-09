<?php

namespace Demo\Sandbox\Resource\Page\First;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Greeting page
 */
class Greeting extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' => 'Hello.'
    ];

    /**
     * @param string $name
     */
    public function onGet($name = 'anonymous')
    {
        $this['greeting'] = $this
            ->resource
            ->get
            ->uri('app://self/first/greeting')
            ->withQuery(['name' => $name])
            ->request();

        return $this;
    }
}
