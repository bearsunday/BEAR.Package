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
        $version = $event->getComposer()->getPackage()->getPrettyVersion();
        $hash = $event->getComposer()->getLocker()->getLockData()['hash'];
        $bearRoot = dirname(__DIR__);
        file_put_contents($bearRoot . '/VERSION', $version);
        file_put_contents($bearRoot . '/ID', $hash);

        $unlink = function ($dirPath) {
            if (! file_exists($dirPath)) {
                return;
            }
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
            rmdir($dirPath);
        };
        $targetHello = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Helloworld';
        $targetSandbox = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Sandbox';
        $helloApp = dirname(__DIR__) . '/apps/Demo.Helloworld';
        $sandboxApp = dirname(__DIR__) . '/apps/Demo.Sandbox';

        @$unlink($helloApp);
        @$unlink($sandboxApp);
        link($targetHello, $helloApp);
        link($targetSandbox, $sandboxApp);

        include $bearRoot . '/bin/env.php';
    }
}
