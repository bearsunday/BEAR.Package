<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Cache;

use BEAR\Sunday\Inject\TmpDirInject;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\ProviderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class CacheProvider implements ProviderInterface
{
    use TmpDirInject;

    /**
     * Return instance
     *
     * @return \Doctrine\Common\Cache\Cache
     */

    /**
     * @var string
     */
    private $cacheNamespace;

    /**
     * @param $cacheNamespace
     *
     * @Inject
     * @Named("cacheNamespace=cache_namespace")
     */
    public function __construct($cacheNamespace = null)
    {
        $this->cacheNamespace = $cacheNamespace;
    }

    /**
     * {@inheritdoc}
     *
     * @return ApcCache|FilesystemCache
     */
    public function get()
    {
        $loaded = extension_loaded('apc') || extension_loaded('apcu');
        if ($loaded && ini_get('apc.enabled')) {
            $apcCache =new ApcCache();
            $apcCache->setNamespace($this->cacheNamespace);

            return $apcCache;
        }

        $fileCache = new FilesystemCache($this->tmpDir . '/cache');
        $fileCache->setNamespace($this->cacheNamespace);

        return $fileCache;
    }
}
