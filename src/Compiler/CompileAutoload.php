<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Types;
use ReflectionClass;

use function assert;
use function class_exists;
use function file_exists;
use function interface_exists;
use function is_float;
use function is_int;
use function memory_get_peak_usage;
use function microtime;
use function number_format;
use function printf;
use function realpath;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function trait_exists;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type ClassList from Types
 * @psalm-import-type ClassPaths from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 */
final class CompileAutoload
{
    /**
     * @param ClassList $classes
     * @param AppDir    $appDir
     * @param Context   $context
     */
    public function __construct(
        private FakeRun $fakeRun,
        private FilePutContents $filePutContents,
        private ArrayObject $classes,
        private string $appDir,
        private string $context,
    ) {
    }

    public function getFileInfo(string $filename): string
    {
        if ($this->filePutContents->isOverwritten($filename)) {
            return $filename . ' (overwritten)';
        }

        return $filename;
    }

    public function __invoke(): int
    {
        ($this->fakeRun)();
        /** @var list<string> $classes */
        $classes = (array) $this->classes;
        $paths = $this->getPaths($classes);
        $autoload = $this->saveAutoloadFile($this->appDir, $paths);
        $start = $_SERVER['REQUEST_TIME_FLOAT'] ?? 0;
        assert(is_float($start));
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        printf("Compilation (2/2) took %f seconds and used %fMB of memory\n", $time, $memory);
        printf("autoload.php: %s\n", $this->getFileInfo($autoload));

        return 0;
    }

    /**
     * @param list<string> $classes
     *
     * @return ClassPaths
     */
    public function getPaths(array $classes): array
    {
        $paths = [];
        $seen = [];
        foreach ($classes as $class) {
            // could be phpdoc tag by annotation loader
            if ($this->isNotAutoloadble($class)) {
                continue;
            }

            /** @var class-string $class */
            $filePath = (string) (new ReflectionClass($class))->getFileName();
            if (! $this->isNotCompileFile($filePath)) {
                continue; // @codeCoverageIgnore
            }

            $pathKey = realpath($filePath);
            $pathKey = $pathKey !== false ? $pathKey : $filePath;
            if (isset($seen[$pathKey])) {
                continue;
            }

            $seen[$pathKey] = true;
            $paths[] = $this->getRelativePath($this->appDir, $filePath);
        }

        return $paths;
    }

    /**
     * Render already-known files as preload/autoload entries.
     *
     * @param list<string> $files
     *
     * @return ClassPaths
     */
    public function getFilePaths(array $files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->getRelativePath($this->appDir, $file);
        }

        return $paths;
    }

    /**
     * @param AppDir     $appDir
     * @param ClassPaths $paths
     */
    public function saveAutoloadFile(string $appDir, array $paths): string
    {
        $requiredFile = '';
        foreach ($paths as $path) {
            $requiredFile .= sprintf(
                "require %s;\n",
                $path,
            );
        }

        $autoloadFile = sprintf("<?php

// %s autoload

require __DIR__ . '/vendor/autoload.php';
%s", $this->context, $requiredFile);
        $appDirRealpath = realpath($appDir);
        assert($appDirRealpath !== false);
        $fileName = $appDirRealpath . '/autoload.php';

        ($this->filePutContents)($fileName, $autoloadFile);

        return $fileName;
    }

    private function isNotAutoloadble(string $class): bool
    {
        return ! class_exists($class, false) && ! interface_exists($class, false) && ! trait_exists($class, false);
    }

    private function isNotCompileFile(string $filePath): bool
    {
        return file_exists($filePath) || is_int(strpos($filePath, 'phar'));
    }

    /**
     * Anchor on the prefix, never a substring: "/data/app" also appears in "/data/app-cache/Foo.php"
     * (which would emit __DIR__ . '-cache/Foo.php') and in "/srv/data/app/Foo.php" (where the old
     * regex matched nothing and emitted an unterminated string literal).
     */
    private function getRelativePath(string $rootDir, string $file): string
    {
        $dir = (string) realpath($rootDir);
        if ($dir !== '' && str_starts_with($file, $dir . DIRECTORY_SEPARATOR)) {
            return sprintf("__DIR__ . '%s'", substr($file, strlen($dir)));
        }

        return sprintf("'%s'", $file);
    }
}
