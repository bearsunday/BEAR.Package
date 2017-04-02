<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

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
        $packageDir = dirname(__DIR__);
        $targetHello = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Helloworld';
        $targetSandbox = dirname(__DIR__) . '/vendor/bear/demo-apps/Demo.Sandbox';
        $helloApp = dirname(__DIR__) . '/apps/Demo.Helloworld';
        $sandboxApp = dirname(__DIR__) . '/apps/Demo.Sandbox';

        if (file_exists($helloApp)) {
            unlink($helloApp);
        }
        if (file_exists($sandboxApp)) {
            unlink($sandboxApp);
        }
        symlink($targetHello, $helloApp);
        symlink($targetSandbox, $sandboxApp);

        include $packageDir . '/bin/bear.env';
    }
}
