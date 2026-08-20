<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

use function sprintf;
use function str_replace;

/** @implements ProviderInterface<SchemeCollectionInterface> */
final class ImportSchemeCollectionProvider implements ProviderInterface
{
    /** @param ImportApp[] $importAppConfig */
    public function __construct(
        #[Named(ImportAppConfig::class)]
        private array $importAppConfig,
        #[Named('original')]
        private SchemeCollectionInterface $schemeCollection,
        private AbstractAppMeta $appMeta,
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

    /** An import writes under the host's tmp and log, so an archive needs no writable tree of its own. */
    private function importMeta(ImportApp $app): Meta
    {
        $name = str_replace('\\', '/', $app->appName);
        $tmp = sprintf('%s/%s/%s/tmp', $this->appMeta->tmpDir, $name, $app->context);
        $log = sprintf('%s/%s/%s/log', $this->appMeta->logDir, $name, $app->context);

        return new Meta($app->appName, $app->context, $app->appDir(), $tmp, $log);
    }
}
