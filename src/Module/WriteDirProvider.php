<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector\AppDirs;
use Override;
use Ray\Di\ProviderInterface;

/**
 * The write directory the application was given, read back from its Meta.
 *
 * A provider, not an instance: it runs at boot, so an override injector with a bespoke Meta
 * gets that Meta's directory rather than the one a compile happened to see.
 *
 * @implements ProviderInterface<string|null>
 */
final class WriteDirProvider implements ProviderInterface
{
    public function __construct(private AbstractAppMeta $appMeta)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): string|null
    {
        return AppDirs::writeDirOf($this->appMeta->name, $this->appMeta->tmpDir);
    }
}
