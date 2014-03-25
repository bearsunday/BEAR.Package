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
    public function get()
    {
        static $compiler;

        if ($compiler) {
            return $compiler;
        }
        $moduleProvider = function() {
            $appModule = "{$this->appName}\Module\AppModule";
            return new $appModule($this->context);
        };
        $cacheKey = $this->appName . $this->context;
        $cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache($this->tmpDir);
        $compiler = DiCompiler::create($moduleProvider, $cache, $cacheKey, $this->tmpDir);

        return $compiler;
    }
}
