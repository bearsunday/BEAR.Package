<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Installer;

use Composer\Script\Event;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Composer callback script
 *
 * @package    BEAR.Package
 */
class Composer
{
    /**
     * @param Event $event
     */
    public static function postInstall(Event $event)
    {
        self::chmodWritable("apps/Helloworld/data");
        self::chmodWritable("apps/Sandbox/data");
        $version = $event->getComposer()->getPackage()->getPrettyVersion();
        file_put_contents('./VERSION', $version);
        echo "Thank you for installing BEAR.Sunday." . PHP_EOL;
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
