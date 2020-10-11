<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\NamedParameterInterface;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

use function in_array;
use function is_callable;
use function sprintf;
use function substr;

final class CompileClassMetaInfo
{
    /**
     * Save annotation and method meta information
     *
     * @param class-string<T> $className
     *
     * @template T
     */
    public function __invoke(Reader $reader, NamedParameterInterface $namedParams, string $className): void
    {
        $class = new ReflectionClass($className);
        $instance = $class->newInstanceWithoutConstructor();
        if (! $instance instanceof $className) {
            return; // @codeCoverageIgnore
        }

        $reader->getClassAnnotations($class);
        $methods = $class->getMethods();
        $log = sprintf('M %s:', $className);
        foreach ($methods as $method) {
            $methodName = $method->getName();
            if ($this->isMagicMethod($methodName)) {
                continue;
            }

            if (substr($methodName, 0, 2) === 'on') {
                $log .= sprintf(' %s', $methodName);
                $this->saveNamedParam($namedParams, $instance, $methodName);
            }

            // method annotation
            $reader->getMethodAnnotations($method);
            $log .= sprintf('@ %s', $methodName);
        }
    }

    private function isMagicMethod(string $method): bool
    {
        return in_array($method, ['__sleep', '__wakeup', 'offsetGet', 'offsetSet', 'offsetExists', 'offsetUnset', 'count', 'ksort', 'asort', 'jsonSerialize'], true);
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
        } catch (ParameterException $e) {
            return;
        }
    }
}
