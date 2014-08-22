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
use Doctrine\Common\Cache\Cache;

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

    private $cache;

    /**
     * @param string $appName
     * @param string|string[] $context
     * @param string $tmpDir
     * @param Cache  $cache
     *
     * @Inject
     * @Named("appName=app_name,context=app_context,tmpDir=tmp_dir")
     */
    public function __construct($appName, $context, $tmpDir, $cache = null)
    {
        $this->appName = $appName;
        $this->context = $context;
        $this->tmpDir = $tmpDir;
        $this->cache = $cache ?: (function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($this->tmpDir));
    }

    /**
     * {@inheritdoc}
     * @return \Ray\Di\DiCompiler
     */
    public function get($extraCacheKey = '')
    {
        $contextKey = is_array($this->context)? implode('_', $this->context) : $this->context;
        $saveKey = $this->appName . $contextKey;
        if (isset(self::$compiler[$saveKey])) {
            return self::$compiler[$saveKey];
        }
        $moduleProvider = function () use ($saveKey) {
            // avoid infinity loop
            if (isset(self::$module[$saveKey])) {
                return self::$module[$saveKey];
            }
            $appModule = "{$this->appName}\Module\AppModule";
            self::$module[$saveKey] = new DiCompilerModule(new $appModule($this->context));

            return self::$module[$saveKey];
        };
        $cacheKey = $this->appName . $contextKey . $extraCacheKey;
        self::$compiler[$saveKey] = $compiler = DiCompiler::create($moduleProvider, $this->cache, $cacheKey, $this->tmpDir);

        return $compiler;
    }
}
