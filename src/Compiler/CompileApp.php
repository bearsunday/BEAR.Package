<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Annotations\Reader;
use Ray\Compiler\CompileInjector;
use Ray\PsrCacheModule\LocalCacheProvider;
use ReflectionClass;

use function assert;
use function in_array;
use function is_callable;
use function microtime;
use function sprintf;
use function str_starts_with;

final class CompileApp
{
    /** @var array{class: int, method: int, time: float} */
    private array $logs = [
        'class' => 0,
        'method' => 0,
        'time' => 0,
    ];

    /**
     * Compile application
     *
     * DI+AOP script file
     * Parameter meta information
     * (No annotation cached)
     *
     * @param list<string> $extraContexts
     *
     * @return array{class: int, method: int, time: float}
     */
    public function compile(CompileInjector $injector, array $extraContexts = []): array
    {
        $start = microtime(true);
        $reader = $injector->getInstance(Reader::class);
        assert($reader instanceof Reader);
        $namedParams = $injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);
        // create DI factory class and AOP compiled class for all resources and save $app cache.
        $app = $injector->getInstance(AppInterface::class);
        assert($app instanceof AppInterface);
        $meta = $injector->getInstance(AbstractAppMeta::class);
        // check resource injection and create annotation cache
        $resources = $meta->getResourceListGenerator();
        foreach ($resources as $resource) {
            $this->logs['class']++;
            [$className] = $resource;
            $this->saveMeta($namedParams, new ReflectionClass($className));
        }

        $this->compileExtraContexts($extraContexts, $meta);
        $this->logs['time'] = (float) sprintf('%.3f', microtime(true) - $start);

        return $this->logs;
    }

    /**
     * Save annotation and method meta information
     *
     * @param ReflectionClass<object> $class
     */
    private function saveMeta(NamedParameterInterface $namedParams, ReflectionClass $class): void
    {
        $instance = $class->newInstanceWithoutConstructor();

        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (! str_starts_with($methodName, 'on')) {
                continue;
            }

            $this->logs['method']++;

            $this->saveNamedParam($namedParams, $instance, $methodName);
        }
    }

    private function saveNamedParam(NamedParameterInterface $namedParameter, object $instance, string $method): void
    {
        // named parameter
        if (! in_array($method, ['onGet', 'onPost', 'onPut', 'onPatch', 'onDelete', 'onHead'], true)) {
            return;  // @codeCoverageIgnore
        }

        $callable = [$instance, $method];
        if (! is_callable($callable)) {
            return;  // @codeCoverageIgnore
        }

        try {
            $namedParameter->getParameters($callable, []);
            // @codeCoverageIgnoreStart
        } catch (ParameterException) {
            return; // It is OK to ignore exceptions. The objective is to obtain meta-information.

            // @codeCoverageIgnoreEnd
        }
    }

    /** @param list<string> $extraContexts */
    public function compileExtraContexts(array $extraContexts, AbstractAppMeta $meta): void
    {
        $cache = (new LocalCacheProvider())->get();
        foreach ($extraContexts as $context) {
            $contextualMeta = new Meta($meta->name, $context, $meta->appDir);
            PackageInjector::getInstance($contextualMeta, $context, $cache)->getInstance(AppInterface::class);
        }
    }
}
