<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Compiler;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Annotations\Reader;

use function assert;
use function class_exists;

/**
 * Compiler Component
 */
final class CompileDiScripts
{
    /** @var Compiler */
    private $compiler;

    /** @var ScanClass */
    private $compilerScanClass;

    public function __construct(Compiler $compiler, ScanClass $compilerScanClass)
    {
        $this->compiler = $compiler;
        $this->compilerScanClass = $compilerScanClass;
    }

    public function __invoke(AbstractAppMeta $appMeta): void
    {
        $reader = $this->compiler->getInjector()->getInstance(Reader::class);
        assert($reader instanceof Reader);
        $namedParams = $this->compiler->getInjector()->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);
        // create DI factory class and AOP compiled class for all resources and save $app cache.
        $app = $this->compiler->getInjector()->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);

        // check resource injection and create annotation cache
        $metas = $appMeta->getResourceListGenerator();
        /** @var array{0: string, 1:string} $meta */
        foreach ($metas as $meta) {
            [$className] = $meta;
            assert(class_exists($className));
            $this->scanClass($reader, $namedParams, $className);
        }
    }

    /**
     * Save annotation and method meta information
     *
     * @param class-string<T> $className
     *
     * @template T
     */
    private function scanClass(Reader $reader, NamedParameterInterface $namedParams, string $className): void
    {
        ($this->compilerScanClass)($reader, $namedParams, $className);
    }
}
