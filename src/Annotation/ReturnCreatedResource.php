<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ReturnCreatedResource
{
}
