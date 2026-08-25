<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final class ImportSchemeCollectionProvider implements ProviderInterface
{
    /** @param ImportApp[] $importAppConfig */
    public function __construct(
        #[Named(ImportAppConfig::class)]
        private array $importAppConfig,
        #[Named('original')]
        private SchemeCollectionInterface $schemeCollection,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): SchemeCollectionInterface
    {
        foreach ($this->importAppConfig as $app) {
            $injector = Injector::fromMeta($this->importMeta($app), $app->context);
            $adapter = new AppAdapter($injector, $app->appName);
            $this->schemeCollection
                ->scheme('page')->host($app->host)->toAdapter($adapter)
                ->scheme('app')->host($app->host)->toAdapter($adapter);
        }

        return $this->schemeCollection;
    }

    /** An imported application is an application: it declares where it writes, or writes in its own tree. */
    private function importMeta(ImportApp $app): Meta
    {
        return new Meta($app->appName, $app->context, $app->appDir());
    }
}
