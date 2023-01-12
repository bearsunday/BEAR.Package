<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SplFileInfo;

use function array_map;
use function file_exists;
use function filemtime;
use function glob;
use function max;
use function preg_quote;
use function sprintf;

final class FileUpdate
{
    private int $updateTime;
    private string $srcRegex;
    private string $varRegex;

    public function __construct(AbstractAppMeta $meta)
    {
        $basePath = preg_quote($meta->appDir . '/', '/');
        $srcPath = $basePath . 'src\/';
        $varPath = $basePath . 'var\/';
        $this->srcRegex = sprintf('/^(?!.*(%s)).*?$/', $srcPath . 'Resource');
        $this->varRegex = sprintf('/^(?!.*(%s|%s|%s|%s)).*?$/', $varPath . 'tmp', $varPath . 'log', $varPath . 'templates', $varPath . 'phinx');
        $this->updateTime = $this->getLatestUpdateTime($meta);
    }

    public function isNotUpdated(AbstractAppMeta $meta): bool
    {
        return $this->getLatestUpdateTime($meta) === $this->updateTime;
    }

    public function getLatestUpdateTime(AbstractAppMeta $meta): int
    {
        $srcFiles = $this->getFiles($meta->appDir . '/src', $this->srcRegex);
        $varFiles = $this->getFiles($meta->appDir . '/var', $this->varRegex);
        $envFiles = (array) glob($meta->appDir . '/.env*');
        $scanFiles = [...$srcFiles, ...$varFiles, ...$envFiles];
        $composerLock = $meta->appDir . '/composer.lock';
        if (file_exists($composerLock)) {
            $scanFiles[] = $composerLock;
        }

        /** @psalm-suppress all -- ignore filemtime could return false */
        return (int) max(array_map([$this, 'filemtime'], $scanFiles));
    }

    /** @SuppressWarnings(PHPMD.UnusedPrivateMethod) */
    private function filemtime(string $filename): string
    {
        return (string) filemtime($filename);
    }

    /** @return list<string> */
    private function getFiles(string $path, string $regex): array
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS,
                ),
                RecursiveIteratorIterator::LEAVES_ONLY,
            ),
            $regex,
            RecursiveRegexIterator::MATCH,
        );

        $files = [];
        /** @var RegexIterator<string, SplFileInfo, RecursiveIteratorIterator> $iterator */
        foreach ($iterator as $fileName => $fileInfo) { // @phpstan-ignore-line
            if (! $fileInfo->isFile() || $fileInfo->getFilename()[0] === '.') {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $files[] = $fileName;
        }

        return $files;
    }
}
