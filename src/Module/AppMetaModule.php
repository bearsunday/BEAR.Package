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

class AppMetaModule extends AbstractModule
{
    private \BEAR\AppMeta\AbstractAppMeta $appMeta;

    public function __construct(AbstractAppMeta $appMeta, ?AbstractModule $module = null)
    {
        $this->appMeta = $appMeta;
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
