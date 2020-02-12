<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Context\Provider\ProdCacheProvider;
use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\Package\Provide\Error\ProdVndErrorPageFactory;
use BEAR\Package\Provide\Logger\ProdMonologProvider;
use BEAR\RepositoryModule\Annotation\Storage;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\VoidOptionsRenderer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
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
    protected function configure() : void
    {
        $this->bind(ErrorPageFactoryInterface::class)->to(ProdVndErrorPageFactory::class);
        $this->bind(LoggerInterface::class)->toProvider(ProdMonologProvider::class)->in(Scope::SINGLETON);
        $this->disableOptionsMethod();
        // prod cache namespace (override in AppInjector)
        $this->bind()->annotatedWith('cache_namespace')->toInstance(microtime());
        // generic cache for user
        $this->bind(Cache::class)->toProvider(ProdCacheProvider::class)->in(Scope::SINGLETON);
        // query repository (shared-server) cache
        $this->bind(CacheProvider::class)->annotatedWith(Storage::class)->toProvider(ProdCacheProvider::class)->in(Scope::SINGLETON);
        // prod annotation reader
        $this->bind(Reader::class)->annotatedWith('annotation_reader')->to(AnnotationReader::class);
        $this->bind(Cache::class)->annotatedWith('annotation_cache')->toProvider(ProdCacheProvider::class)->in(Scope::SINGLETON);
        $this->bind(Reader::class)->toConstructor(
            CachedReader::class,
            'reader=annotation_reader'
        );
    }

    /**
     * Disable OPTIONS resource request method in production
     *
     * OPTIONS method return 405 Method Not Allowed error code. To enable OPTIONS in `prod` context,
     * Install BEAR\Resource\Module\OptionsMethodModule() in your ProdModule.
     */
    private function disableOptionsMethod() : void
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(VoidOptionsRenderer::class);
    }
}
