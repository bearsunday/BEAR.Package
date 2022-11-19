<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Provide\Error\ErrorPageFactoryInterface;
use BEAR\Package\Provide\Error\ProdVndErrorPageFactory;
use BEAR\Package\Provide\Logger\ProdMonologProvider;
use BEAR\QueryRepository\ProdQueryRepositoryModule;
use BEAR\RepositoryModule\Annotation\EtagPool;
use BEAR\Resource\NullOptionsRenderer;
use BEAR\Resource\RenderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Ray\Compiler\DiCompileModule;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Ray\PsrCacheModule\Annotation\Local;
use Ray\PsrCacheModule\LocalCacheProvider;
use Ray\PsrCacheModule\Psr6LocalCacheModule;

/** @codeCoverageIgnore */
class ProdModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(ErrorPageFactoryInterface::class)->to(ProdVndErrorPageFactory::class);
        $this->bind(LoggerInterface::class)->toProvider(ProdMonologProvider::class)->in(Scope::SINGLETON);
        $this->disableOptionsMethod();
        $this->installCacheModule();
        $this->install(new DiCompileModule(true));
    }

    private function installCacheModule(): void
    {
        $this->install(new ProdQueryRepositoryModule());
        $this->install(new Psr6LocalCacheModule());
        /** @psalm-suppress DeprecatedClass */
        $this->bind(CacheItemInterface::class)->annotatedWith(EtagPool::class)->toProvider(LocalCacheProvider::class);
        $this->bind(Reader::class)->toConstructor(
            PsrCachedReader::class,
            ['reader' => 'dual_reader', 'cache' => Local::class],
        )->in(Scope::SINGLETON);
        $this->bind(Reader::class)->annotatedWith('dual_reader')->toConstructor(
            DualReader::class,
            ['annotationReader' => 'annotation_reader', 'attributeReader' => 'attribute_reader'],
        );
        $this->bind(Reader::class)->annotatedWith('annotation_reader')->to(AnnotationReader::class);
        $this->bind(Reader::class)->annotatedWith('attribute_reader')->to(AttributeReader::class);
    }

    /**
     * Disable OPTIONS resource request method in production
     *
     * OPTIONS method return 405 Method Not Allowed error code. To enable OPTIONS in `prod` context,
     * Install BEAR\Resource\Module\OptionsMethodModule() in your ProdModule.
     */
    private function disableOptionsMethod(): void
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(NullOptionsRenderer::class);
    }
}
