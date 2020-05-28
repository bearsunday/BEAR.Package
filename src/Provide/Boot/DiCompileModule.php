<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Boot;

use Ray\Di\AbstractModule;

class DiCompileModule extends AbstractModule
{
    /**
     * @var bool
     */
    private $doCompile;

    public function __construct(bool $doCompile, AbstractModule $module = null)
    {
        $this->doCompile = $doCompile;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind()->annotatedWith(self::class)->toInstance($this->doCompile);
    }
}
