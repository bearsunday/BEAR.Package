<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class ApiRouter implements RouterInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * {@inheritdoc}
     */
    public function match(array $globals = [])
    {
        $request = new RouterMatch;
        $method = strtolower($globals['_SERVER']['REQUEST_METHOD']);
        list($request->method, $request->path, $request->query) = [
            $method,
            'app://self' . parse_url($globals['_SERVER']['REQUEST_URI'], PHP_URL_PATH),
            ($method === 'get') ? $globals['_GET'] : $globals['_POST']
        ];

        return $request;
    }
}
