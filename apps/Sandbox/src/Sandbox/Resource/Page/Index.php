<?php
namespace Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Index page
 */
class Index extends Page
{
    use ResourceInject;

    /**
     * @var array
     */
    public $body = [
        'greeting' => 'Hello BEAR.Sunday',
        'version' => [],
        'extensions' => [],
        'is_cli_server' => false,
        'performance' => ''
    ];

    /**
     * @var array
     */
    public $links = [
        'helloworld' => [Link::HREF => 'page://self/hello/world'],
        'blog' => [Link::HREF => 'page://self/blog/posts'],
        'restbucks' => [Link::HREF => 'page://self/restbucks/index'],
        'demo' => [Link::HREF => 'page://self/demo/index'],
    ];

    /**
     * @Inject
     * @Named("package_dir")
     */
    public function __construct($packageDir)
    {
        $bearVersion = file_get_contents($packageDir . '/VERSION');
        $this['version'] = [
            'php' => phpversion(),
            'BEAR' => $bearVersion,
            'extensions' => $this->extensionVersion()
        ];
        $this['is_cli_server'] = (php_sapi_name() === 'cli-server');
    }

    public function onGet()
    {
        // page speed.
        $this['performance'] = $this->resource->get->uri('app://self/performance')->request();

        return $this;
    }

    /**
     * @return array
     */
    private function extensionVersion()
    {
        return [
            'apc' => extension_loaded('apc') ? phpversion('apc') : 'n/a',
            'curl' => extension_loaded('curl') ? 'yes' : 'n/a',
            'memcache' => extension_loaded('memcache') ? phpversion('memcache') : 'n/a',
            'mysqlnd' => extension_loaded('mysqlnd') ? phpversion('mysqlnd') : 'n/a',
            'pdo_sqlite' => extension_loaded('pdo_sqlite') ? phpversion('pdo_sqlite') : 'n/a',
            'Xdebug' => extension_loaded('Xdebug') ? phpversion('Xdebug') : 'n/a',
            'xhprof' => extension_loaded('xhprof') ? phpversion('xhprof') : 'n/a'
        ];
    }
}
