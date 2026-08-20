<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A compile step's binding key names its subdirectory of the build directory.
 *
 * It must be a single path segment of [A-Za-z0-9_-] and not "di", which the compiled DI
 * scripts own: anything else would wipe or escape the build directory when the step runs.
 */
final class InvalidStepKeyException extends LogicException
{
}
