<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The preload.php on disk compiles another build's DI scripts.
 *
 * One is written per compile, at a fixed path, and the last compile wins. Packing a context
 * that was compiled earlier would carry a preload naming a build the archive does not hold,
 * and a server started with it would preload nothing it goes on to use.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharPreloadForAnotherBuildException extends LogicException
{
    public function __construct(string $preload, string $expected)
    {
        parent::__construct(sprintf('%s, expected %s', $preload, $expected));
    }
}
