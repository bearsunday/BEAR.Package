<?php
namespace Demo\Sandbox\Resource\Page;

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
        'greeting' => '',
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
            'BEAR' => $bearVersion
        ];
        $this['is_cli_server'] = (php_sapi_name() === 'cli-server');
    }

    /**
     * @param string $name
     */
    public function onGet($name = 'World')
    {
        $this['greeting'] = 'Hello ' . $name;
        $this['performance'] = $this->resource->get->uri('app://self/performance')->request();

        return $this;
    }
}
