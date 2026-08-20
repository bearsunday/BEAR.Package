<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\SchemeCollectionInterface;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

use function dirname;
use function str_replace;
use function str_starts_with;

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
        // The host's base: an import is placed beside it.
        $writeDir = self::writeBaseOf($this->appMeta);
        foreach ($this->importAppConfig as $app) {
            $injector = Injector::getInstance($app->appName, $app->context, $app->appDir(), null, $writeDir);
            $adapter = new AppAdapter($injector, $app->appName);
            $this->schemeCollection
                ->scheme('page')->host($app->host)->toAdapter($adapter)
                ->scheme('app')->host($app->host)->toAdapter($adapter);
        }

        return $this->schemeCollection;
    }

    /**
     * The base the host writes under, derived from tmpDir: Meta::create() lays it out as
     * {base}/{Vendor}/{Project}/{context}/tmp; a tmpDir under appDir means the default
     * var/ layout and no separate base.
     *
     * @return non-empty-string|null
     */
    private static function writeBaseOf(AbstractAppMeta $meta): string|null
    {
        $tmpDir = str_replace('\\', '/', $meta->tmpDir);
        $appDir = str_replace('\\', '/', $meta->appDir);
        if (str_starts_with($tmpDir, $appDir . '/')) {
            return null;
        }

        /** @var non-empty-string $base */
        $base = dirname($meta->tmpDir, 4);

        return $base;
    }
}
