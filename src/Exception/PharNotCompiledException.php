<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * The context has no compiled DI scripts to pack.
 *
 * `Compiler::phar()` packs what is on disk: `$compiler(); $compiler->phar();` is the order. The
 * compile marker is what proves a compile ran, and the boot inside the archive reads it too.
 */
final class PharNotCompiledException extends LogicException
{
    public function __construct(string $scriptDir)
    {
        parent::__construct($scriptDir);
    }
}
