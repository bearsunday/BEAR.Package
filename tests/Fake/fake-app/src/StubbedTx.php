<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class StubbedTx
{
}
