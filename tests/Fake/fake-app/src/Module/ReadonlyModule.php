<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;

use function sys_get_temp_dir;

/**
 * An application that says where it writes, for a tree it does not own.
 *
 * The paths are named, not derived from the tree, so the declaration a compile puts in the
 * scripts is the one a boot from those scripts reads back.
 */
final class ReadonlyModule extends AbstractModule
{
    /** @return non-empty-string */
    public static function tmpDir(): string
    {
        return self::base() . '/tmp';
    }

    /** @return non-empty-string */
    public static function logDir(): string
    {
        return self::base() . '/log';
    }

    /** @return non-empty-string */
    private static function base(): string
    {
        return sys_get_temp_dir() . '/bear-fake-readonly-app';
    }

    protected function configure(): void
    {
        $this->install(new ReadOnlyAppModule(self::tmpDir(), self::logDir()));
    }
}
