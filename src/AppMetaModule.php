<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\AbstractModule;

class AppMetaModule extends AbstractModule
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta, AbstractModule $module = null)
    {
        $this->appMeta = $appMeta;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $this->bind(AppInterface::class)->to($this->appMeta->name . '\Module\App');
        $this->bind()->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
    }
}
