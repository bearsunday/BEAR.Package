<?php
/**
 * Sandbox
 *
 * @package Sandbox
 */
namespace Sandbox;

use BEAR\Package\Provide\Application\AbstractApp as PackageApp;

require_once dirname(dirname(__DIR__)) . '/vendor/smarty/smarty/distribution/libs/Smarty.class.php';
require_once dirname(dirname(__DIR__)) . '/vendor/twig/twig/lib/Twig/Autoloader.php';

\Twig_Autoloader::register();
require __DIR__ . '/scripts/apc_safe.php';

/**
 * Application
 *
 * @package Sandbox
 */
final class App extends PackageApp
{
    /** application dir path @var string */
    const DIR = __DIR__;
}
