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
use function array_merge;
use function file_exists;
use function glob;
use function max;
use function preg_quote;
use function sprintf;

final class FileUpdate
{
    /** @var int */
    private $updateTime;

    /** @var string */
    private $srcRegex;

    /** @var string */
    private $varRegex;

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
        $scanFiles = array_merge($srcFiles, $varFiles, $envFiles);
        $composerLock = $meta->appDir . '/composer.lock';
        if (file_exists($composerLock)) {
            $scanFiles[] = $composerLock;
        }

        /** @psalm-suppress all -- ignore filemtime could return false */
        return (int) max(array_map('filemtime', $scanFiles));
    }

    /**
     * @return list<string>
     */
    private function getFiles(string $path, string $regex): array
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            ),
            $regex,
            RecursiveRegexIterator::MATCH
        );

        $files = [];
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileName => $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getFilename()[0] !== '.') {
                $files[] = (string) $fileName;
            }
        }

        return $files;
    }
}
