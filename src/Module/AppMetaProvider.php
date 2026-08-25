<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

use function str_replace;
use function strlen;
use function substr;
use function sys_get_temp_dir;

/**
 * The one hook a compiled container runs at boot, which is the only place a machine can answer.
 *
 * A directory the declaration left out keeps the shape the application would have used inside
 * its own tree, moved under the machine's temp directory. Nothing is recorded to work that out:
 * the name is the Meta's, and the rest is the part of its path below its own appDir.
 *
 * @implements ProviderInterface<AbstractAppMeta>
 */
final class AppMetaProvider implements ProviderInterface
{
    public const IN_THE_TREE = 'bear_package_writes_in_the_tree';

    public function __construct(
        #[Named(self::IN_THE_TREE)]
        private AbstractAppMeta $inTheTree,
        private WriteDirs $declared,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): AbstractAppMeta
    {
        $meta = clone $this->inTheTree;
        $meta->tmpDir = $this->declared->tmpDir ?? $this->underMachineTemp($this->inTheTree->tmpDir);
        $meta->logDir = $this->declared->logDir ?? $this->underMachineTemp($this->inTheTree->logDir);

        return $meta;
    }

    /**
     * @param non-empty-string $inTheTree
     *
     * @return non-empty-string
     */
    private function underMachineTemp(string $inTheTree): string
    {
        $below = substr($inTheTree, strlen($this->inTheTree->appDir));

        return str_replace('\\', '/', sys_get_temp_dir()) . '/' . str_replace('\\', '/', $this->inTheTree->name) . $below;
    }
}
