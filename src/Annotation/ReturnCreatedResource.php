<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class ReturnCreatedResource
{
}
