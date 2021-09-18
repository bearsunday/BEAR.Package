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
use function glob;
use function max;
use function preg_quote;
use function sprintf;

final class BindingsUpdate
{
    /** @var int */
    private $updateTime;

    public function __construct(AbstractAppMeta $meta)
    {
        $this->updateTime = $this->getLatestUpdateTime($meta);
    }

    public function isUpdated(AbstractAppMeta $meta): bool
    {
        return $this->getLatestUpdateTime($meta) !== $this->updateTime;
    }

    public function getLatestUpdateTime(AbstractAppMeta $meta): int
    {
        $basePath = preg_quote($meta->appDir . '/', '/');
        $srcPath = $basePath . 'src\/';
        $varPath = $basePath . 'var\/';
        $srcRegex =  sprintf('/^(?!.*(%s)).*?$/', $srcPath . 'Resource');
        $srcFiles = $this->getFiles($meta->appDir . '/src', $srcRegex);
        $varRegex =  sprintf('/^(?!.*(%s|%s|%s|%s)).*?$/', $varPath . 'tmp', $varPath . 'log', $varPath . 'templates', $varPath . 'phinx');
        $varFiles = $this->getFiles($meta->appDir . '/var', $varRegex);
        $envFiles = glob($meta->appDir . '/.env*');
        $scanFiles = $srcFiles + $varFiles + $envFiles;

        return max(array_map('filemtime', $scanFiles));
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
