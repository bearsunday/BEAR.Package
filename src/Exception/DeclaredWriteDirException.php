<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A declared write directory that no deployment can resolve.
 *
 * The declaration is compiled in and read wherever the build runs, so it names one place. A
 * relative path names wherever the process started, which a compile and a request disagree on.
 *
 * @see \BEAR\Package\Module\ReadOnlyAppModule
 */
final class DeclaredWriteDirException extends LogicException
{
}
