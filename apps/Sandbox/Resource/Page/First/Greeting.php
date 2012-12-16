<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page\First;

use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Greeting page
 *
 * @package    Sandbox
 * @subpackage Resource
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
