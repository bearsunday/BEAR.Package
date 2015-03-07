<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Transfer;

use BEAR\RepositoryModule\Annotation\Storage;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Injector;

class Http304
{
    /**
     * @param string $appName application name (Vendor\Package)
     * @param array  $server  $_SERVER
     *
     * @return bool
     */
    public function isNotModified($appName ,array $server)
    {
        if(! isset($server['HTTP_IF_NONE_MATCH'])) {
            return false;
        }

        $kvs = apc_fetch($appName . 'etag-kvs');
        if (! $kvs) {
            $prodModule = $appName . '\Module\ProdModule';
            $kvs = (new Injector(new $prodModule))->getInstance(Cache::class, Storage::class);
            apc_store($appName . 'etag-kvs', $kvs);
        }

        /* @var $kvs Cache */
        if(($kvs->contains('etag-id:' . stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])))) {
            return true;
        }

        return false;
    }
}
