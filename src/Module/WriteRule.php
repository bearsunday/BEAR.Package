<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Types;

use function str_replace;
use function sys_get_temp_dir;

/**
 * Only names, contexts and declared paths are held, so one rule serves every machine that boots
 * the build. An application nested under another names its parent here, and the parent's own
 * rule is asked at the same moment.
 *
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 */
final class WriteRule
{
    /**
     * @param AppName $name
     * @param Context $context
     */
    public function __construct(
        public readonly string $name,
        public readonly string $context,
        public readonly WriteDirs|null $declared = null,
        public readonly WriteRule|null $parent = null,
    ) {
    }

    public function needsBoot(): bool
    {
        if ($this->declared === null) {
            return true;
        }

        return $this->declared->tmpDir === null || $this->declared->logDir === null;
    }

    /** @return non-empty-string */
    public function tmpDir(): string
    {
        $declared = $this->declared?->tmpDir;

        return $declared ?? $this->base('tmp');
    }

    /** @return non-empty-string */
    public function logDir(): string
    {
        $declared = $this->declared?->logDir;

        return $declared ?? $this->base('log');
    }

    /** @return non-empty-string */
    private function base(string $dir): string
    {
        if ($this->parent !== null) {
            $parent = $dir === 'tmp' ? $this->parent->tmpDir() : $this->parent->logDir();

            return $parent . '/' . $this->key() . '/' . $dir;
        }

        if ($this->declared === null) {
            return self::forwardSlashed(AbstractAppMeta::appDir($this->name)) . '/var/' . $dir . '/' . $this->context;
        }

        return self::forwardSlashed(sys_get_temp_dir()) . '/' . $this->key() . '/' . $dir;
    }

    /**
     * Meta keeps a write directory exactly as it arrives, so the spelling is settled here:
     * concatenating onto a Windows temp directory mixes both separators into one path.
     *
     * @return non-empty-string
     */
    private static function forwardSlashed(string $dir): string
    {
        return str_replace('\\', '/', $dir) ?: '/';
    }

    /** @return non-empty-string */
    private function key(): string
    {
        return str_replace('\\', '/', $this->name) . '/' . $this->context;
    }
}
