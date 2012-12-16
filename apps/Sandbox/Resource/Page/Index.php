<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\Page;

use BEAR\Sunday\Resource\AbstractPage as Page;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\ResourceInject;
use BEAR\Sunday\Framework\Framework;
use Ray\Di\Di\Inject;

/**
 * Index page
 *
 * @package    Sandbox
 * @subpackage Resource
 */
class Index extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' =>  'Hello BEAR.Sunday !',
        'version' => '',
        'loaded_extensions' => []
    ];

    /**
     * @var array
     */
    public $links = [
        'helloworld' => [Link::HREF => 'page://self/hello/world'],
        'blog' => [Link::HREF => 'page://self/blog/posts'],
        'restbucks' => [Link::HREF => 'page://self/restbucks/index']
    ];

    public function __construct()
    {
        $this['version'] = [
            'php' => phpversion(),
            'BEAR' => Framework::VERSION
        ];
        $this['extensions'] = [
            'apc' => extension_loaded('apc') ? phpversion('apc') : 'n/a',
            'curl' => extension_loaded('curl') ? 'yes' : 'n/a',
            'memcache' => extension_loaded('memcache') ? phpversion('memcache') : 'n/a',
            'mysqlnd' => extension_loaded('mysqlnd') ? phpversion('mysqlnd') : 'n/a',
            'pdo_sqlite' => extension_loaded('pdo_sqlite') ? phpversion('pdo_sqlite') : 'n/a',
            'Xdebug' => extension_loaded('Xdebug') ? phpversion('Xdebug') : 'n/a',
            'xhprof' => extension_loaded('xhprof') ? phpversion('xhprof') : 'n/a'
        ];
    }

    public function onGet()
    {
        $cache = (PHP_SAPI !== 'cli') ? apc_cache_info('user') : ['num_entries' => 0, 'mem_size' => 0];
        $this['apc'] = [
            'total' => $cache['num_entries'],
            'size' => $cache['mem_size']
        ];
        // page speed.
        $this['performance'] = $this->resource->get->uri('app://self/performance')->request();

        return $this;
    }
}
