<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use Override;
use Stringable;
use Throwable;

final class LogRef implements Stringable
{
    private string $ref;

    public function __construct(Throwable $e)
    {
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFallbackGlobalName
        $this->ref = hash('crc32b', $e::class . $e->getMessage() . $e->getFile() . $e->getLine());
    }

    #[Override]
    public function __toString(): string
    {
        return $this->ref;
    }
}
