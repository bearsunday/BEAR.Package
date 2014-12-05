<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Bootstrap;

use Ray\Di\Injector;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;

final class Bootstrap
{
    /**
     * @param string $appName
     * @param string $contexts
     * @param Cache  $cache
     *
     * @return AppInterface
     */
    public function newApp($appName, $contexts = 'app', Cache $cache)
    {
        $app = $cache->fetch($contexts);
        if ($app) {
            return $app;
        }
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = array_reduce($contextsArray, function($carry, $item) use ($appName) {
            $class = $appName . '\Module\\' . ucwords($item) . 'Module';
            return new $class($carry);
        });
        $app = (new Injector($module))->getInstance(AppInterface::class);
        $cache->save($contexts, $app);

        return $app;
    }
}
