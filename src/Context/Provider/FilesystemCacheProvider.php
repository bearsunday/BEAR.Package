<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Context\Provider;

use BEAR\AppMeta\AbstractAppMeta;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\ProviderInterface;

class FilesystemCacheProvider implements ProviderInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $app;

    public function __construct(AbstractAppMeta $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return new FilesystemCache($this->app->tmpDir . '/cache');
    }
}
