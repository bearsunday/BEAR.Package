<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Types;
use Override;
use Ray\Di\AbstractModule;

/**
 * Default write bindings: inside the application's own tree.
 *
 * Installed innermost, so an application's own module overrides by ordinary precedence.
 *
 * @psalm-import-type Context from Types
 */
final class WriteModule extends AbstractModule
{
    /** @param Context $context */
    public function __construct(
        private AbstractAppMeta $appMeta,
        private string $context,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(WriteDirs::class)->toInstance(new WriteDirs($this->appMeta->tmpDir, $this->appMeta->logDir));
        $this->bind(WriteShape::class)->toInstance(new WriteShape('var/tmp/' . $this->context, 'var/log/' . $this->context));
    }
}
