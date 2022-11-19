<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Extension\Application\AppInterface;
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
 */
class AppMetaModule extends AbstractModule
{
    public function __construct(private AbstractAppMeta $appMeta, AbstractModule|null $module = null)
    {
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $appClass = $this->appMeta->name . '\Module\App';
        assert(class_exists($appClass));
        $this->bind(AppInterface::class)->to($appClass)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
    }
}
