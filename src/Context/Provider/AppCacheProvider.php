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

class AppCacheProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $appCacheDir;

    public function __construct(AbstractAppMeta $appMeta)
    {
        $cacheDir = $appMeta->tmpDir . '/app';
        ! file_exists($cacheDir) && mkdir($cacheDir);
        $this->appCacheDir = $cacheDir;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return new FilesystemCache($this->appCacheDir);
    }
}
