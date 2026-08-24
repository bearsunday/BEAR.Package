<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Compile\CompileStepInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Instance;
use Ray\Di\MultiBinder;
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
 * Set<CompileStepInterface>
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
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta());
        $appClass = $this->appMeta->name . '\Module\App';
        assert(class_exists($appClass));
        $this->bind(AppInterface::class)->to($appClass)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
        // Declared empty: an app that installs no step still injects a Map, not Unbound.
        MultiBinder::newInstance($this, CompileStepInterface::class);
    }

    /**
     * Cloned, not rebuilt: an archive that moves has its appDir re-pointed on unserialize, and
     * only tmp and log are the declaration's to give.
     */
    private function appMeta(): AbstractAppMeta
    {
        $dirs = $this->declaredWriteDirs();
        if ($dirs === null) {
            return $this->appMeta;
        }

        $meta = clone $this->appMeta;
        $meta->tmpDir = $dirs->tmpDir;
        $meta->logDir = $dirs->logDir;

        return $meta;
    }

    /**
     * Read by index: resolving would construct whatever else the chain bound, and this runs
     * before any binding of this module's own exists.
     */
    private function declaredWriteDirs(): WriteDirs|null
    {
        if (! $this->lastModule instanceof AbstractModule) {
            return null;
        }

        $dependency = $this->lastModule->getContainer()->getContainer()[WriteDirs::class . '-'] ?? null;

        return $dependency instanceof Instance && $dependency->value instanceof WriteDirs ? $dependency->value : null;
    }
}
