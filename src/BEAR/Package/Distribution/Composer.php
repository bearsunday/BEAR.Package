<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Distribution;

use Composer\Script\Event;

/**
 * Composer callback script
 *
 * @package    BEAR.Package
 */
class Installer
{
    public static function preInstall(Event $event) {
        $io = $event->getIO();
        /** @var $io \Composer\IO\ConsoleIO */
        if ($io->askConfirmation("Are you sure you want to proceed? ", false)) {
            // ok, continue on to composer install
            return true;
        }
        // exit composer and terminate installation process
        exit;
    }

    public static function postPackageInstall(Event $event)
    {
        $installedPackage = $event->getComposer()->getPackage();
        array_map('chmod', glob("{$appDir}"), [, 777]);
    }
}