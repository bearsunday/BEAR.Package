<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Compiler;
use Composer\Autoload\ClassLoader;
use ReflectionClass;

use function assert;
use function class_exists;
use function dirname;
use function file_exists;
use function interface_exists;
use function is_int;
use function is_string;
use function realpath;
use function str_starts_with;
use function strpos;
use function trait_exists;

use const DIRECTORY_SEPARATOR;

/**
 * Filters tracker-recorded classes for preload.php generation.
 */
final class PreloadClassFilter
{
    private string $composerDir = '';

    /** @var array<string, true> */
    private array $composerLoadedFiles = [];

    public function __construct(
        private ClassLoader $loader,
    ) {
        $this->prepareComposerFiles();
    }

    public function __invoke(string $class): bool
    {
        if ($this->isExcludedClass($class) || ! $this->isDeclared($class)) {
            return false;
        }

        /** @var class-string $class */
        $reflection = new ReflectionClass($class);
        if ($reflection->isAnonymous()) {
            return false;
        }

        $filePath = $this->resolvedAutoloadPath($class, $reflection);
        if ($filePath === null) {
            return false;
        }

        return ! $this->isComposerLoadedFile($filePath);
    }

    private function isDeclared(string $class): bool
    {
        return class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false);
    }

    /**
     * @param class-string            $class
     * @param ReflectionClass<object> $reflection
     */
    private function resolvedAutoloadPath(string $class, ReflectionClass $reflection): string|null
    {
        $fileName = $reflection->getFileName();
        $loaderFile = $this->loader->findFile($class);
        if (! is_string($fileName) || ! is_string($loaderFile)) {
            return null;
        }

        $filePath = $this->normalizePath($fileName);
        $loaderPath = $this->normalizePath($loaderFile);
        if (! is_string($filePath) || $filePath !== $loaderPath) {
            return null;
        }

        return $filePath;
    }

    /**
     * The compiler's own machinery is never part of a boot.
     *
     * Nothing else is excluded by name: preload is recorded by booting the application,
     * so a class is in the list because that boot loaded it.
     */
    public function isExcludedClass(string $class): bool
    {
        return is_int(strpos($class, Compiler::class));
    }

    private function prepareComposerFiles(): void
    {
        $classLoaderFile = (new ReflectionClass(ClassLoader::class))->getFileName();
        assert(is_string($classLoaderFile));
        $composerDir = $this->normalizePath(dirname($classLoaderFile));
        assert(is_string($composerDir));
        $this->composerDir = $composerDir;

        $autoloadFilesPath = $composerDir . '/autoload_files.php';
        // @codeCoverageIgnoreStart
        if (! file_exists($autoloadFilesPath)) {
            return;
        }

        // @codeCoverageIgnoreEnd

        /** @var array<string, string> $autoloadFiles */
        $autoloadFiles = require $autoloadFilesPath;
        foreach ($autoloadFiles as $autoloadFile) {
            $path = $this->normalizePath($autoloadFile);
            // @codeCoverageIgnoreStart
            if (! is_string($path)) {
                continue;
            }

            // @codeCoverageIgnoreEnd

            $this->composerLoadedFiles[$path] = true;
        }
    }

    private function isComposerLoadedFile(string $filePath): bool
    {
        return str_starts_with($filePath, $this->composerDir . DIRECTORY_SEPARATOR)
            || isset($this->composerLoadedFiles[$filePath]);
    }

    private function normalizePath(string $path): string|false
    {
        // @codeCoverageIgnoreStart
        if (str_starts_with($path, 'phar://')) {
            return $path;
        }

        // @codeCoverageIgnoreEnd

        return realpath($path);
    }
}
