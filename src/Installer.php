<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\Sunday\Exception\LogicException;
use Composer\Script\Event;

/**
 * Composer callback script
 */
class Installer
{
    /**
     * @param Event $event
     */
    public static function packageUpdate(Event $event)
    {
        $composer = $event->getComposer();
        $version = $event->getComposer()->getPackage()->getPrettyVersion();
        $hash = $event->getComposer()->getLocker()->getLockData()['hash'];
        $bearRoot = dirname(__DIR__);
        file_put_contents($bearRoot . '/VERSION', $version);
        file_put_contents($bearRoot . '/ID', $hash);

        $targetHello = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Helloworld';
        $targetSandbox = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Sandbox';
        $helloApp = dirname(__DIR__) . '/apps/Demo.Helloworld';
        $sandboxApp = dirname(__DIR__) . '/apps/Demo.Sandbox';
        if (! file_exists($helloApp)) {
            link($targetHello, $helloApp);
            link($targetSandbox, $sandboxApp);
        }

        include $bearRoot . '/bin/env.php';
    }
}
