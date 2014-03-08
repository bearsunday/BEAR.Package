<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Bootstrap;

use BEAR\Package\Dev\Application\ApplicationReflector;
use BEAR\Package\Provide\Application\AbstractApp;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Di\Annotation;
use Ray\Di\CacheInjector;
use Ray\Di\Config;
use Ray\Di\Container;
use Ray\Di\Definition;
use Ray\Di\Forge;
use Ray\Di\Injector;
use Ray\Di\Logger as DiLogger;
use Koriym\FusionCache\DoctrineCache as FusionCache;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\DiCompiler;
use Ray\Di\Logger;

/**
 * Return application instance
 *
 * @param $appName
 * @param $context
 * @param null $tmpDir
 *
 * @return \BEAR\Sunday\Extension\Application\AppInterface|object
 */
function getApp($appName, $context, $tmpDir)
{
    $injector = function () use ($appName, $context, $tmpDir) {
        $appModule = "{$appName}\Module\AppModule";
        return new Injector(
            new Container(new Forge(new Config(new Annotation(new Definition, new AnnotationReader)))),
            new $appModule($context),
            new Bind,
            new Compiler(
                $tmpDir,
                new PHPParser_PrettyPrinter_Default
            ),
            new DiLogger
        );
    };

    $cache = function_exists('apc_fetch') ?
        new FusionCache(new ApcCache, function () use ($tmpDir) {return new FilesystemCache($tmpDir);})
        : new FilesystemCache($tmpDir);
    $app = DiCompiler::create($injector, $cache, $appName . $context)->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
    /* @var $app \BEAR\Sunday\Extension\Application\AppInterface */
    return $app;
}
