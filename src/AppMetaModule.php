<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(AbstractAppMeta::class)->toInstance($this->appMeta);
        $this->bind(AppInterface::class)->to($this->appMeta->name . '\Module\App');
        $this->bind('')->annotatedWith(AppName::class)->toInstance($this->appMeta->name);
    }
}
