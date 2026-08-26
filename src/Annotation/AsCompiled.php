<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/** The Meta a compile wrote into the container, before boot settles its write directories. */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class AsCompiled
{
}
