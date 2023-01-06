<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Injector;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final class ImportSchemeCollectionProvider implements ProviderInterface
{
    /** @param ImportApp[] $importAppConfig */
    #[Named('importAppConfig=BEAR\Resource\Annotation\ImportAppConfig,schemeCollection=original')]
    public function __construct(
        #[Named(ImportAppConfig::class)]
        private array $importAppConfig,
        #[Named('original')]
        private SchemeCollectionInterface $schemeCollection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get(): SchemeCollectionInterface
    {
        foreach ($this->importAppConfig as $app) {
            $injector = Injector::getInstance($app->appName, $app->context, $app->appDir);
            $adapter = new AppAdapter($injector, $app->appName);
            $this->schemeCollection
                ->scheme('page')->host($app->host)->toAdapter($adapter)
                ->scheme('app')->host($app->host)->toAdapter($adapter);
        }

        return $this->schemeCollection;
    }
}
