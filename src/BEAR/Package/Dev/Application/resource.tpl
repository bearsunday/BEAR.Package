<?php
/**
 * @package    {$app}
 * @subpackage Resource
 */
namespace {$namespace};

use BEAR\Resource\AbstractObject;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * untitled
 *
 * @package    {$app}
 * @subpackage Resource
 */
class {$class} extends AbstractObject
{
    use ResourceInject;

    public $body = [
        'greeting' =>  ''
    ];

    public function onGet()
    {
        return $this;
    }
}
