<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ReverseLinkInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;

final class RouterReverseLink implements ReverseLinkInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(string $uri) : string
    {
        $routeName = (string) parse_url($uri, PHP_URL_PATH);
        $urlQuery = parse_url($uri, PHP_URL_QUERY);
        $urlQuery ? parse_str($urlQuery, $value) : $value = [];
        if ($value === []) {
            return $uri;
        }
        $reverseUri = $this->router->generate($routeName, $value);
        if (\is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }
}
