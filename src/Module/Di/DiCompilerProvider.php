<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Di;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\DiCompiler;
use Ray\Di\ProviderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class DiCompilerProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var \Ray\Di\DiCompiler
     */
    private static $compiler;

    /**
     * @var \Ray\Di\AbstractModule[]
     */
    private static $module;

    /**
     * @param string $appName
     * @param string $context
     * @param string $tmpDir
     *
     * @Inject
     * @Named("appName=app_name,context=app_context,tmpDir=tmp_dir")
     */
    public function __construct($appName, $context, $tmpDir)
    {
        $this->appName = $appName;
        $this->context = $context;
        $this->tmpDir = $tmpDir;
    }

    /**
     * {@inheritdoc}
     * @return \Ray\Di\DiCompiler
     */
    public function get($extraCacheKey = '')
    {
        $saveKey = $this->appName . $this->context;
        if (isset(self::$compiler[$saveKey])) {
            return self::$compiler[$saveKey];
        }

        $moduleProvider = function () use ($saveKey) {
            // avoid infinity loop
            if (isset(self::$module[$saveKey])) {
                return self::$module[$saveKey];
            }
            $appModule = "{$this->appName}\Module\AppModule";
            self::$module[$saveKey] = new $appModule($this->context);

            return self::$module[$saveKey];
        };
        $cacheKey = $this->appName . $this->context . $extraCacheKey;

        $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($this->tmpDir);
        self::$compiler[$saveKey] = $compiler = DiCompiler::create($moduleProvider, $cache, $cacheKey, $this->tmpDir);

        return $compiler;
    }
}
