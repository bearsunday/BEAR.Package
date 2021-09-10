<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

use function array_combine;
use function array_map;
use function array_merge;
use function arsort;
use function key;
use function sprintf;

final class BindingsUpdate
{
    /** @var string */
    private $latestFile;

    /** @var int */
    private $updateTime;

    public function __construct(AbstractAppMeta $meta)
    {
        $files = $this->sortFiles($meta);
        $this->latestFile = key($files);
        $this->updateTime = $files[key($files)];
    }

    public function isUpdated($meta): bool
    {
        $files = $this->sortFiles($meta);
        $updateTime = $files[key($files)];

        return $updateTime !== $this->updateTime;
    }

    public function sortFiles($meta): array
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
        foreach ($iterator as $fileName => $class) {
            if ($class->isFile()) {
                $files[] = $fileName;
            }
        }

        return $files;
    }
}
