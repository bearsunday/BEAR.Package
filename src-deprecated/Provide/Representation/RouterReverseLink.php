<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ReverseLinkInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;

use function is_string;
use function parse_str;
use function parse_url;

use const PHP_URL_PATH;
use const PHP_URL_QUERY;

final class RouterReverseLink implements ReverseLinkInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function __invoke(string $uri): string
    {
        $routeName = (string) parse_url($uri, PHP_URL_PATH);
        $urlQuery = parse_url($uri, PHP_URL_QUERY);
        $urlQuery ? parse_str($urlQuery, $value) : $value = [];
        if ($value === []) {
            return $uri;
        }

        /** @var array<string, mixed> $value */
        $reverseUri = $this->router->generate($routeName, $value);
        if (is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }
}
