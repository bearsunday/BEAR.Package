<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Resource\Exception\UriException;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class CliRouter implements RouterInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     *
     * @Inject
     * @Named("original")
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $globals = [])
    {
        list(, $method, $uri) = $globals['argv'];
        if (! filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new UriException($uri);
        }
        $parsedUrl = parse_url($uri);
        $query = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }

        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $parsedUrl['path']
            ],
            '_GET' => $query,
            '_POST' => $query
        ];

        return $this->router->match($globals);
    }
}
