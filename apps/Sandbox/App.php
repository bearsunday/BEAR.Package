<?php
/**
 * Sandbox
 *
 * @package Sandbox
 */
namespace Sandbox;

use BEAR\Package\Provide\Application\AbstractApp;

require_once dirname(dirname(__DIR__)) . '/vendor/smarty/smarty/distribution/libs/Smarty.class.php';
require_once dirname(dirname(__DIR__)) . '/vendor/twig/twig/lib/Twig/Autoloader.php';
\Twig_Autoloader::register();

/**
 * Application
 *
 * @package Sandbox
 */
final class App extends AbstractApp
{
    /** application dir path @var string */
    const DIR = __DIR__;
}
