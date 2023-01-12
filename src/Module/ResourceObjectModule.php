<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Provide\Error\NullPage;
use BEAR\Resource\ResourceObject;
use Generator;
use Ray\Di\AbstractModule;

/**
 * Bind all resource object
 */
class ResourceObjectModule extends AbstractModule
{
    /** @param Generator<array{0: class-string<ResourceObject>, 1: string}> $resourceObjects */
    public function __construct(
        private Generator $resourceObjects,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->install(new \BEAR\Resource\Module\ResourceObjectModule($this->getResourceObjects()));
        $this->bind(NullPage::class);
    }

    /** @return Generator<class-string<ResourceObject>> */
    private function getResourceObjects(): Generator
    {
        foreach ($this->resourceObjects as [$class]) {
            yield $class;
        }
    }
}
