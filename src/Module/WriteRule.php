<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;

use function str_replace;
use function sys_get_temp_dir;

/**
 * Only names, contexts and declared paths are held, so one rule serves every machine that boots
 * the build. An application nested under another names its parent here, and the parent's own
 * rule is asked at the same moment.
 */
final class WriteRule
{
    public function __construct(
        public readonly AppId $app,
        public readonly WriteDirs|null $declared = null,
        public readonly WriteRule|null $parent = null,
    ) {
    }

    public function needsBoot(): bool
    {
        return $this->declared?->tmpDir === null || $this->declared->logDir === null;
    }

    /** @return non-empty-string */
    public function tmpDir(): string
    {
        return $this->declared->tmpDir ?? $this->base('tmp');
    }

    /** @return non-empty-string */
    public function logDir(): string
    {
        return $this->declared->logDir ?? $this->base('log');
    }

    /** @return non-empty-string */
    private function base(string $dir): string
    {
        if ($this->parent !== null) {
            $parent = $dir === 'tmp' ? $this->parent->tmpDir() : $this->parent->logDir();

            return $parent . '/' . $this->key() . '/' . $dir;
        }

        if ($this->declared === null) {
            return self::forwardSlashed(AbstractAppMeta::appDir($this->app->name)) . '/var/' . $dir . '/' . $this->app->context;
        }

        return self::forwardSlashed(sys_get_temp_dir()) . '/' . $this->key() . '/' . $dir;
    }

    /**
     * Meta spells a directory forward-slashed whatever the platform, and a declaration reaches
     * it unchanged: a resolved one has to arrive the same way or the two disagree on Windows.
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
        return str_replace('\\', '/', $this->app->name) . '/' . $this->app->context;
    }
}
