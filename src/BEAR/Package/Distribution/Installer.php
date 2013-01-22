<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Distribution;

use Composer\Script\Event;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Composer callback script
 *
 * @package    BEAR.Package
 */
class Installer
{
    /**
     * @param Event $event
     */
    public static function postPackageInstall(Event $event)
    {
        self::chmodWritable("apps/Helloworld/data");
        self::chmodWritable("apps/Sandbox/data");
    }

    /**
     * @param $path
     */
    private static function chmodWritable($path)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach($iterator as $item) {
            chmod($item, 0777);
        }
    }
}
