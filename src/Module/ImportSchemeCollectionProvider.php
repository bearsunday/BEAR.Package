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

use function explode;
use function sprintf;

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
        [$vendor, $project] = explode('\\', $app->appName);
        $base = sprintf('%s/%s/%s/%s', $this->appMeta->tmpDir, $vendor, $project, $app->context);
        $logBase = sprintf('%s/%s/%s/%s', $this->appMeta->logDir, $vendor, $project, $app->context);

        return new Meta($app->appName, $app->context, $app->appDir(), $base . '/tmp', $logBase . '/log');
    }
}
