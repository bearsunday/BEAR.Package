<?php
/**
 * @package    HelloWorld
 * @subpackage Resource
 */
namespace Helloworld\Resource\Page;

use BEAR\Resource\AbstractObject as Page;

/**
 * Hello world - min
 *
 * @package    HelloWorld
 * @subpackage Resource
 */
class Minhello extends Page
{
    /**
     * @var string
     */
    public $body = 'Hello World !';

    /**
     * @return Minhello
     */
    public function onGet()
    {
        return $this;
    }
}
