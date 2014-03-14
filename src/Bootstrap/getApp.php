<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Bootstrap;

use BEAR\Package\Module\Di\DiCompilerProvider;

/**
 * Return application instance
 *
 * @param $appName
 * @param $context
 * @param null $tmpDir
 *
 * @return \BEAR\Sunday\Extension\Application\AppInterface
 */
function getApp($appName, $context, $tmpDir)
{
    $diCompiler = (new DiCompilerProvider($appName, $context, $tmpDir))->get();
    $app = $diCompiler->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
    /** $app \BEAR\Sunday\Extension\Application\AppInterface */

    return $app;
}
