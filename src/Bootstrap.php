<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Injector;

final class Bootstrap
{
    const PACKAGE_MODULE_PATH = 'BEAR\Package\Context\\';

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
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = self::PACKAGE_MODULE_PATH . ucwords($context) . 'Module';
            }
            $module =  new $class($module);
        }
        $app = (new Injector($module, $appMeta->tmpDir))->getInstance(AppInterface::class);
        $cache->save($contexts, $app);

        return $app;
    }
}
