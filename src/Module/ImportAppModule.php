<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\Module\SchemeCollectionProvider;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\NotFound;

/**
 * Provides SchemeCollectionInterface and derived bindings
 *
 * The following bindings are provided:
 *
 * SchemeCollectionInterface
 * SchemeCollectionInterface:original
 * :ImportAppConfig
 */
final class ImportAppModule extends AbstractModule
{
    /**
     * Import scheme config
     *
     * @var array<ImportApp>
     */
    private array $importApps = [];

    /** @param array<ImportApp> $importApps */
    public function __construct(array $importApps)
    {
        foreach ($importApps as $importApp) {
            // create import config
            $this->importApps[] = $importApp;
        }

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFound
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(ImportAppConfig::class)->toInstance($this->importApps);
        $this->bind(SchemeCollectionInterface::class)->annotatedWith('original')->toProvider(SchemeCollectionProvider::class);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }
}
