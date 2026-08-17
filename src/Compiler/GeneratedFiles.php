<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Types;

use function is_file;
use function rename;
use function unlink;

/**
 * What a compile leaves at the application root.
 *
 * @psalm-import-type AppDir from Types
 */
final class GeneratedFiles
{
    public const NAMES = ['preload.php', 'autoload.php', 'app.phar'];

    private const HELD = '.rollback';

    /** @param array<string, string> $held root path => holding path */
    private function __construct(private readonly array $held)
    {
    }

    /**
     * Held beside each file, so moving a 30MB archive costs no copy.
     *
     * @param AppDir $appDir
     */
    public static function stash(string $appDir): self
    {
        $held = [];
        foreach (self::NAMES as $name) {
            $file = $appDir . '/' . $name;
            if (! is_file($file)) {
                continue;
            }

            $held[$file] = $file . self::HELD;
            rename($file, $held[$file]);
        }

        return new self($held);
    }

    public function restore(): void
    {
        foreach ($this->held as $file => $holding) {
            rename($holding, $file);
        }
    }

    public function discard(): void
    {
        foreach ($this->held as $holding) {
            unlink($holding);
        }
    }
}
