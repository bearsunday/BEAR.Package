<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * The script directory holds no build of this application, and cannot be written to.
 *
 * A production context boots from compiled scripts, and compiles them on demand when they are
 * missing - which an archive or an immutable image does not allow. Compile the tree before
 * deploying it: `php bin/compile.php {context}`.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/production.html#compilation-recommended
 */
final class NotCompiledException extends RuntimeException
{
}
