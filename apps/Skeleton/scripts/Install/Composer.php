<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Skeleton\scripts\Install;

use Composer\Script\Event;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Composer script
 */
class Composer
{
    /**
     * Composer post install script
     *
     * @param Event $event
     */
    public static function postInstall(Event $event = null)
    {
        $appName = $event->getIO()->ask('Input new application name', 'MyApp');
        $appName = ucwords($appName);
        $jobChmod = function (\SplFileInfo $file) {
            chmod($file, 0777);
        };
        $jobRename = function (\SplFileInfo $file) use ($appName) {
            $fineName = $file->getFilename();
            if ($fineName === '.' || $fineName === '..' ) {
                return;
            }
            $contents = file_get_contents($file);
            $contents = str_replace('Skeleton', $appName, $contents);
            file_put_contents($file, $contents);
        };

        $skeletonRoot = dirname(dirname(dirname(dirname(__DIR__))));
        // chmod
        self::recursiveJob("{$skeletonRoot}/data", $jobChmod);

        // rename file contents
        self::recursiveJob($skeletonRoot, $jobRename);

        // rename app folder
        $newName = str_replace('Skeleton', $appName, $skeletonRoot);
        rename($skeletonRoot, $newName);
    }

    /**
     * @param string   $path
     * @param Callable $job
     */
    private static function recursiveJob($path, Callable $job)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach($iterator as $file) {
            $job($file);
        }
    }
}
