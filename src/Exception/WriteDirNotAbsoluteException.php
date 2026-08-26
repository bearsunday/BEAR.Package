<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A declared write directory is relative.
 *
 * The declaration is compiled into the container and read again wherever the build boots, so a
 * relative path resolves against the boot process's working directory - `/` under php-fpm, the
 * project root under the test runner. Declare an absolute path, or none.
 */
final class WriteDirNotAbsoluteException extends LogicException
{
}
