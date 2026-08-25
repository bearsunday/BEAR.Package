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
use function str_replace;

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
     * Declaring nothing leaves the Meta this boot resolved, whose paths __wakeup() re-points when
     * the tree has moved. A declaration replaces tmp and log with what it named, and whatever it
     * left out stays relative until the machine that boots prefixes its own temp directory.
     */
    private function bindAppMeta(): void
    {
        $dirs = $this->declaredWriteDirs();
        if ($dirs === null) {
            $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);

            return;
        }

        if ($dirs->tmpDir !== null && $dirs->logDir !== null) {
            $this->bind(AbstractAppMeta::class)->toInstance($this->writing($dirs->tmpDir, $dirs->logDir));

            return;
        }

        $key = str_replace('\\', '/', $this->appMeta->name) . '/' . $this->context;
        $template = $this->writing($dirs->tmpDir ?? $key . '/tmp', $dirs->logDir ?? $key . '/log');
        $this->bind(AbstractAppMeta::class)->annotatedWith(AppMetaProvider::TEMPLATE)->toInstance($template);
        $this->bind(AbstractAppMeta::class)->toProvider(AppMetaProvider::class)->in(Scope::SINGLETON);
    }

    /**
     * @param non-empty-string $tmpDir
     * @param non-empty-string $logDir
     */
    private function writing(string $tmpDir, string $logDir): Meta
    {
        return new Meta($this->appMeta->name, $this->context, $this->appMeta->appDir, $tmpDir, $logDir);
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
