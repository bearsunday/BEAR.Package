<?php
/**
 * @package    Skeleton
 * @subpackage Resource
 */
namespace Skeleton\Resource\Page;

use BEAR\Resource\AbstractObject as Page;
use BEAR\Sunday\Inject\ResourceInject;

/**
 * Index page
 *
 * @package    Skeleton
 * @subpackage Resource
 */
class Index extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' =>  ''
    ];

    public function __construct()
    {
    }

    public function onGet($name = 'BEAR.Sunday')
    {
        $this['greeting'] = 'Hello ' . $name;
        return $this;
    }
}
