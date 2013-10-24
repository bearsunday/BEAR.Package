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
        $version = $event->getComposer()->getPackage()->getPrettyVersion();
        $hash = $event->getComposer()->getLocker()->getLockData()['hash'];
        $bearRoot = dirname(dirname(dirname(__DIR__)));
        file_put_contents($bearRoot . '/VERSION', $version);
        file_put_contents($bearRoot . '/ID', $hash);
    }

    /**
     * @since 0.10.0
     */
    public static function setUp()
    {
        $executable = CurrentPhpExecutable::getExecutable();
        $configFile = CurrentPhpExecutable::getConfigFile();
        passthru(escapeshellarg($executable) . ($configFile === null ? '' : (' -c ' . escapeshellarg($configFile))) . ' ' . escapeshellarg(dirname(dirname(dirname(__DIR__))) . '/bin/setup.php'));
    }

    /**
     * @since 0.10.0
     */
    public static function checkEnvironment()
    {
        $executable = CurrentPhpExecutable::getExecutable();
        $configFile = CurrentPhpExecutable::getConfigFile();
        passthru(escapeshellarg($executable) . ($configFile === null ? '' : (' -c ' . escapeshellarg($configFile))) . ' ' . escapeshellarg(dirname(dirname(dirname(__DIR__))) . '/bin/env.php'));
    }

    /**
     * @since 0.10.0
     */
    public static function compile()
    {
        $executable = CurrentPhpExecutable::getExecutable();
        $configFile = CurrentPhpExecutable::getConfigFile();
        passthru(escapeshellarg($executable) . ($configFile === null ? '' : (' -c ' . escapeshellarg($configFile))) . ' ' . escapeshellarg(dirname(dirname(dirname(__DIR__))) . '/apps/Sandbox/bin/compiler.php'));
    }
}
