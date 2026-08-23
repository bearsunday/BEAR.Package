<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * A declared write directory that no deployment can resolve.
 *
 * The declaration is compiled in and read again wherever the build runs, so it has to name the
 * same place every time. A relative path names whatever directory the process happens to be
 * started from, which a compile, a request under fpm and a CLI run each answer differently.
 *
 * @see \BEAR\Package\Module\ReadOnlyAppModule
 */
final class DeclaredWriteDirException extends LogicException
{
}
