<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Types;
use ReflectionClass;

use function assert;
use function class_exists;
use function file_exists;
use function in_array;
use function interface_exists;
use function is_float;
use function is_int;
use function memory_get_peak_usage;
use function microtime;
use function number_format;
use function preg_quote;
use function preg_replace;
use function printf;
use function realpath;
use function sprintf;
use function str_contains;
use function strpos;
use function trait_exists;

/**
 * @psalm-import-type ClassList from Types
 * @psalm-import-type OverwrittenFiles from Types
 * @psalm-import-type ClassPaths from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 */
final class CompileAutoload
{
    /**
     * @param OverwrittenFiles $overwritten
     * @param ClassList        $classes
     * @param AppDir           $appDir
     * @param Context          $context
     */
    public function __construct(
        private FakeRun $fakeRun,
        private FilePutContents $filePutContents,
        private Meta $appMeta,
        private ArrayObject $overwritten,
        private ArrayObject $classes,
        private string $appDir,
        private string $context,
    ) {
    }

    public function getFileInfo(string $filename): string
    {
        if (in_array($filename, (array) $this->overwritten, true)) {
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
        $autoload = $this->saveAutoloadFile($this->appMeta->appDir, $paths);
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

            $paths[] = $this->getRelativePath($this->appDir, $filePath);
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

%s
require __DIR__ . '/vendor/autoload.php';
", $this->context, $requiredFile);
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

    private function getRelativePath(string $rootDir, string $file): string
    {
        $dir = (string) realpath($rootDir);
        if (str_contains($file, $dir)) {
            return (string) preg_replace('#^' . preg_quote($dir, '#') . '#', "__DIR__ . '", $file) . "'";
        }

        return sprintf("'%s'", $file);
    }
}
