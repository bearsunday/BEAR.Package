<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use BEAR\Resource\Exception\BadRequestException;

use function sprintf;

/**
 * The request line holds no path.
 *
 * PHP accepts request lines that `parse_url()` cannot read a path from - "//" and "///" among
 * them - and a path is what the router turns into a resource URI.
 */
final class InvalidRequestUriException extends BadRequestException implements ExceptionInterface
{
    public function __construct(string $requestUri)
    {
        parent::__construct(sprintf('No path in request URI "%s".', $requestUri));
    }
}
