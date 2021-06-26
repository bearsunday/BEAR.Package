<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\InjectorInterface;

use function assert;
use function class_exists;

final class CompileDiScripts
{
    /** @var CompileClassMetaInfo */
    private $compilerScanClass;

    /** @var InjectorInterface */
    private $injector;

    public function __construct(CompileClassMetaInfo $compilerScanClass, InjectorInterface $injector)
    {
        $this->compilerScanClass = $compilerScanClass;
        $this->injector = $injector;
    }

    public function __invoke(AbstractAppMeta $appMeta): void
    {
        $reader = $this->injector->getInstance(Reader::class);
        assert($reader instanceof Reader);
        $namedParams = $this->injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);
        // create DI factory class and AOP compiled class for all resources and save $app cache.
        $app = $this->injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);

        // check resource injection and create annotation cache
        $metas = $appMeta->getResourceListGenerator();
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
