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

use function array_combine;
use function array_map;
use function array_merge;
use function arsort;
use function key;
use function sprintf;

final class BindingsUpdate
{
    /** @var int */
    private $updateTime;

    public function __construct(AbstractAppMeta $meta)
    {
        $files = $this->sortFiles($meta);
        $this->updateTime = (int) $files[key($files)];
    }

    public function isUpdated(AbstractAppMeta $meta): bool
    {
        $files = $this->sortFiles($meta);
        $updateTime = (int) $files[key($files)];

        return $updateTime !== $this->updateTime;
    }

    /**
     * @return array<string, false|int>
     */
    public function sortFiles(AbstractAppMeta $meta): array
    {
        $modulePath = sprintf('%s/%s', $meta->appDir, 'src/Module');
        $varPath = sprintf('%s/%s', $meta->appDir, 'var');
        $files = array_merge($this->getFiles($modulePath), $this->getFiles($varPath));
        $files = array_combine(
            $files,
            array_map('filemtime', $files)
        );
        arsort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private function getFiles(string $path): array
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^(?!.*log|tmp).+\.?$/',
            RecursiveRegexIterator::MATCH
        );

        $files = [];
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileName => $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = (string) $fileName;
            }
        }

        return $files;
    }
}
