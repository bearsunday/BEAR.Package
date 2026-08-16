<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Annotation\AppDir;
use BEAR\Package\Annotation\WriteDir;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Extension\Application\AppInterface;
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

use function assert;
use function class_exists;

/**
 * Provides AbstractAppMeta and derived bindings
 *
 * The following bindings are provided:
 *
 * AbstractAppMeta
 * AppInterface
 * :AppName
 * :AppDir
 * :WriteDir
 *
 * @psalm-suppress ClassMustBeFinal
 */
class AppMetaModule extends AbstractModule
{
    public function __construct(private AbstractAppMeta $appMeta, AbstractModule|null $module = null)
    {
        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $appClass = $this->appMeta->name . '\Module\App';
        assert(class_exists($appClass));
        $this->bind(AppInterface::class)->to($appClass)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
        $this->bind()->annotatedWith(AppDir::class)->toInstance($this->appMeta->appDir);
        // A derivation, not a property: the provider reads it back from whatever Meta is bound.
        $this->bind()->annotatedWith(WriteDir::class)->toProvider(WriteDirProvider::class);
    }
}
