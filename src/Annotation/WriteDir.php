<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * The writable base the application was given, null when it writes under its own var/.
 *
 * Bound from the Meta the injector was built with, so an imported application receives the
 * host's without anyone naming it twice.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class WriteDir
{
}
