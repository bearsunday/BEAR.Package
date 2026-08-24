<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * No build for this application and context, in a tree that cannot be given one.
 *
 * A boot compiles on demand where the script directory is writable. Where it is not, the build
 * has to arrive with the deployment: compile before packing.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#run
 */
final class NotCompiledException extends RuntimeException
{
}
