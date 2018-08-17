<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Sunday\Extension\Application\AbstractApp;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\Cache;

final class App
{
    /**
     * @param string $name     application name      'MyVendor\MyProject'
     * @param string $contexts application context   'prod-app'
     * @param string $envFile  .env file path        '/path/to/project/.env'
     * @param string $appDir   application directory '/path/to/project'
     */
    public function __invoke(
        string $name,
        string $contexts,
        string $envFile = '',
        string $appDir = ''
    ) : AbstractApp {
        $appMeta = new Meta($name, $contexts, $appDir);
        $cacheNs = (string) filemtime($appMeta->appDir . '/src');
        $injector = new AppInjector($appMeta->name, $contexts, $appMeta, $cacheNs, $envFile);
        $cache = $injector->getCachedInstance(Cache::class);
        $appId = $appMeta->name . $contexts . $cacheNs;
        $app = $cache->fetch($appId);
        if ($app instanceof AbstractApp) {
            return $app;
        }
        $injector->clear();
        $app = $injector->getCachedInstance(AppInterface::class);
        $cache->save($appId, $app);

        return $app;
    }
}
