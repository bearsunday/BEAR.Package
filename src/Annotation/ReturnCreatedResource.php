<?php

declare(strict_types=1);

namespace BEAR\Package\Annotation;

use Attribute;

/**
 * Renders a 201 response with the resource that its `Location` header points to
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/tutorial.html#returncreatedresource
 * @see \BEAR\Package\Provide\Representation\CreatedResourceInterceptor
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ReturnCreatedResource
{
}
