<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/** The application directory, so nothing has to find it from a class name. */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class AppDir
{
}
