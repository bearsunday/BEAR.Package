<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

/** Where an application writes within a tree, relative to the tree's root. */
final class WriteShape
{
    /**
     * @param non-empty-string $tmp
     * @param non-empty-string $log
     */
    public function __construct(
        public readonly string $tmp,
        public readonly string $log,
    ) {
    }
}
