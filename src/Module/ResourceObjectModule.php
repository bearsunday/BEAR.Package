<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Provide\Error\NullPage;
use Generator;
use Ray\Di\AbstractModule;

/**
 * Bind all resource object
 */
class ResourceObjectModule extends AbstractModule
{
    /** @var Generator<array{0: class-string, 1: string}> */
    private $resourceObjects;

    /** @param Generator<array{0: class-string, 1: string}> $resourceObjects */
    public function __construct(
        Generator $resourceObjects,
    ) {
        $this->resourceObjects = $resourceObjects;
        parent::__construct();
    }

    protected function configure(): void
    {
        foreach ($this->resourceObjects as [$class]) {
            $this->bind($class);
        }

        $this->bind(NullPage::class);
    }
}
