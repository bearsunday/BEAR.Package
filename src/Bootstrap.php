<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use Ray\Di\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;

final class Bootstrap
{
    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     * @param Cache           $cache
     *
     * @return AppInterface
     */
    public function newApp(AbstractAppMeta $appMeta, $contexts, Cache $cache)
    {
        $app = $cache->fetch($contexts);
        if ($app) {
            return $app;
        }
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = array_reduce($contextsArray, function ($carry, $item) use ($appMeta) {
            $class = $appMeta->name . '\Module\\' . ucwords($item) . 'Module';
            return new $class($carry);
        });
        $app = (new Injector($module, $appMeta->tmpDir))->getInstance(AppInterface::class);
        $cache->save($contexts, $app);

        return $app;
    }
}
