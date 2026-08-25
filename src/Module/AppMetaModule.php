<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Types;
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
 * @psalm-import-type Context from Types
 * @psalm-suppress ClassMustBeFinal
 */
class AppMetaModule extends AbstractModule
{
    /** @param Context $context */
    public function __construct(
        private AbstractAppMeta $appMeta,
        private string $context,
        AbstractModule|null $module = null,
        private WriteRule|null $parent = null,
    ) {
        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bindAppMeta();
        $appClass = $this->appMeta->name . '\Module\App';
        assert(class_exists($appClass));
        $this->bind(AppInterface::class)->to($appClass)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
        // Declared empty: an app that installs no step still injects a Map, not Unbound.
        MultiBinder::newInstance($this, CompileStepInterface::class);
    }

    /**
     * A declared application is built from the appDir this boot resolved, not from a fresh
     * reflection: an archive that moved has had it re-pointed on unserialize, and two trees of
     * one application in one process each keep their own. Anything the machine has a say in
     * cannot be a value at all.
     */
    private function bindAppMeta(): void
    {
        $dirs = $this->declaredWriteDirs();
        $rule = new WriteRule($this->appMeta->name, $this->context, $dirs, $this->parent);
        $this->bind(WriteRule::class)->toInstance($rule);

        if ($dirs === null && $this->parent === null) {
            $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);

            return;
        }

        if (! $rule->needsBoot()) {
            $meta = new Meta($this->appMeta->name, $this->context, $this->appMeta->appDir, $rule->tmpDir(), $rule->logDir());
            $this->bind(AbstractAppMeta::class)->toInstance($meta);

            return;
        }

        $this->bind(AbstractAppMeta::class)->toProvider(AppMetaProvider::class)->in(Scope::SINGLETON);
    }

    /**
     * Read by index: resolving would construct whatever else the chain bound.
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
