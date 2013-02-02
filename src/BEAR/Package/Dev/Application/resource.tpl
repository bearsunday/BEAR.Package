<?php
/**
 * @package    {$app}
 * @subpackage Resource
 */
namespace {$namespace};

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * untitled
 *
 * @package    {$app}
 * @subpackage Resource
 */
class {$class} extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' =>  ''
    ];

    public function onGet()
    {
        return $this;
    }
}
