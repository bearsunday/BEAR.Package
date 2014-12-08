<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\Resource\Exception\UriException;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;

class CliRouter implements RouterInterface
{
    /**
     * {@inheritdoc}
     */
    public function match(array $globals = [])
    {
        $request = new RouterMatch;
        list(, $method, $uri) = $globals['argv'];
        if (! filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new UriException($uri);
        }
        $parsedUrl = parse_url($uri);
        $query = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
        }
        list($request->method, $request->path, $request->query) = [
            $method,
            'page://self' . $parsedUrl['path'],
            $query
        ];

        return $request;
    }
}
