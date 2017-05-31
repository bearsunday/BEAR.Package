<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Context;

use BEAR\Package\Context\Provider\FilesystemCacheProvider;
use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\Package\Provide\Error\ProdVndErrorPageFactory;
use BEAR\Package\Provide\Logger\ProdMonologProviver;
use BEAR\RepositoryModule\Annotation\Storage;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\VoidOptionsRenderer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Psr\Log\LoggerInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * @codeCoverageIgnore
 */
class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(ErrorPageFactoryInterface::class)->to(ProdVndErrorPageFactory::class);
        $this->bind(LoggerInterface::class)->toProvider(ProdMonologProviver::class)->in(Scope::SINGLETON);
        $this->disableOptionsMethod();
        if (PHP_SAPI !== 'cli' && function_exists('apcu_fetch') && class_exists(ApcuCache::class)) {
            $this->installApcuCache(ApcuCache::class);

            return;
        }
        $this->installFileCache();
    }

    /**
     * Disable OPTIONS resource request method in production
     *
     * OPTIONS method return 405 Method Not Allowed error code. To enable OPTIONS in `prod` context,
     * Install BEAR\Resource\Module\OptionsMethodModule() in your ProdModule.
     */
    private function disableOptionsMethod()
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(VoidOptionsRenderer::class);
    }

    private function installApcuCache($apcClass)
    {
        $this->bind(Cache::class)->to($apcClass)->in(Scope::SINGLETON);
        $this->bind(Reader::class)->toConstructor(
            CachedReader::class,
            'reader=annotation_reader'
        );
        $this->bind(Reader::class)->annotatedWith('annotation_reader')->to(AnnotationReader::class);
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->to($apcClass)->in(Scope::SINGLETON);
        $this->bind(Cache::class)->annotatedWith(Storage::class)->to($apcClass)->in(Scope::SINGLETON);
    }

    private function installFileCache()
    {
        $this->bind(Cache::class)->toProvider(FilesystemCacheProvider::class)->in(Scope::SINGLETON);
        $this->bind(Reader::class)->toConstructor(
            CachedReader::class,
            'reader=annotation_reader'
        );
        $this->bind(Reader::class)->annotatedWith('annotation_reader')->to(AnnotationReader::class);
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->toProvider(FilesystemCacheProvider::class)->in(Scope::SINGLETON);
        $this->bind(Cache::class)->annotatedWith(Storage::class)->toProvider(FilesystemCacheProvider::class)->in(Scope::SINGLETON);
    }
}
