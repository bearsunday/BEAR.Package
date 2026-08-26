<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Annotation\AsCompiled;
use Override;
use Ray\Di\ProviderInterface;

use function hash;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

/**
 * Composes the Meta the application runs on: directories the module tree left null fall
 * to the machine's temp directory, in the tree's own shape.
 *
 * @implements ProviderInterface<AbstractAppMeta>
 */
final class AppMetaProvider implements ProviderInterface
{
    public function __construct(
        #[AsCompiled]
        private AbstractAppMeta $asCompiled,
        private WriteDirs $dirs,
        private WriteShape $shape,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): AbstractAppMeta
    {
        $meta = clone $this->asCompiled;
        $meta->tmpDir = $this->dirs->tmpDir ?? $this->machineTempDir($this->shape->tmp);
        $meta->logDir = $this->dirs->logDir ?? $this->machineTempDir($this->shape->log);

        return $meta;
    }

    /**
     * Keyed by appDir: two checkouts of one application must not share caches.
     *
     * @param non-empty-string $shape
     *
     * @return non-empty-string
     */
    private function machineTempDir(string $shape): string
    {
        return sprintf(
            '%s/%s/%s/%s',
            str_replace('\\', '/', sys_get_temp_dir()),
            str_replace('\\', '/', $this->asCompiled->name),
            hash('xxh128', $this->asCompiled->appDir),
            $shape,
        );
    }
}
