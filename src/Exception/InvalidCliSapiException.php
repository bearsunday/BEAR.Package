<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

final class InvalidCliSapiException extends LogicException
{
    public function __construct(string $sapi)
    {
        parent::__construct(sprintf('The cli context requires the CLI SAPI. Use a non-cli context such as "prod-html-app" for web requests. Current SAPI: %s', $sapi));
    }
}
