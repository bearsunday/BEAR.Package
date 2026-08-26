<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Annotation\AsCompiled;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Compile\CompileStepInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Override;
use Ray\Di\AbstractModule;
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
 * The Meta given here is the compiled one: it names the application and its source tree, and its
 * write directories are settled at boot by {@see AppMetaProvider} from what the module tree says.
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
        $this->bind(AbstractAppMeta::class)->annotatedWith(AsCompiled::class)->toInstance($this->appMeta);
        $this->bind(AbstractAppMeta::class)->toProvider(AppMetaProvider::class)->in(Scope::SINGLETON);
        $appClass = $this->appMeta->name . '\Module\App';
        assert(class_exists($appClass));
        $this->bind(AppInterface::class)->to($appClass)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
        // Declared empty: an app that installs no step still injects a Map, not Unbound.
        MultiBinder::newInstance($this, CompileStepInterface::class);
    }
}
