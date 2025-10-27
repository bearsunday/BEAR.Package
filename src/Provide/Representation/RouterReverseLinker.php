<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Resource\ReverseLinkerInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Override;

use function is_string;
use function parse_url;

use const PHP_URL_PATH;

final class RouterReverseLinker implements ReverseLinkerInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function __invoke(string $uri, array $query): string
    {
        $routeName = (string) parse_url($uri, PHP_URL_PATH);

        $reverseUri = $this->router->generate($routeName, $query);
        if (is_string($reverseUri)) {
            return $reverseUri;
        }

        return $uri;
    }
}
