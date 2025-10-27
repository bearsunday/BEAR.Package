<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_map;
use function assert;
use function file_exists;
use function filemtime;
use function glob;
use function is_string;
use function max;
use function preg_match;
use function preg_quote;
use function rtrim;
use function sprintf;
use function str_replace;

use const DIRECTORY_SEPARATOR;

final class FileUpdate
{
    private int $updateTime;
    private string $srcRegex;
    private string $varRegex;

    public function __construct(AbstractAppMeta $meta)
    {
        $normalizedAppDir = str_replace('\\', '/', rtrim($meta->appDir, '\\/')) . '/';
        $this->srcRegex = sprintf('#^(?!.*(%ssrc/Resource)).*?$#m', $normalizedAppDir);
        $this->varRegex = sprintf(
            '#^(?!%s(?:var%stmp|var%slog|var%stemplates|var%sphinx)).*$#',
            preg_quote($normalizedAppDir, '#'),
            preg_quote(DIRECTORY_SEPARATOR, '#'),
            preg_quote(DIRECTORY_SEPARATOR, '#'),
            preg_quote(DIRECTORY_SEPARATOR, '#'),
            preg_quote(DIRECTORY_SEPARATOR, '#'),
        );
        $this->updateTime = $this->getLatestUpdateTime($meta);
    }

    public function isNotUpdated(AbstractAppMeta $meta): bool
    {
        return $this->getLatestUpdateTime($meta) === $this->updateTime;
    }

    public function getLatestUpdateTime(AbstractAppMeta $meta): int
    {
        $srcFiles = $this->getFiles($meta->appDir . DIRECTORY_SEPARATOR . 'src', $this->srcRegex);
        $varFiles = $this->getFiles($meta->appDir . DIRECTORY_SEPARATOR . 'var', $this->varRegex);
        $envFilesResult = glob($meta->appDir . DIRECTORY_SEPARATOR . '.env*');
        $envFiles = $envFilesResult === false ? [] : $envFilesResult;
        $scanFiles = [...$srcFiles, ...$varFiles, ...$envFiles];
        $composerLock = $meta->appDir . DIRECTORY_SEPARATOR . 'composer.lock';
        if (file_exists($composerLock)) {
            $scanFiles[] = $composerLock;
        }

        $fileTimes = array_map([$this, 'filemtime'], $scanFiles);
        assert($fileTimes !== []);

        return (int) max($fileTimes);
    }

    /** @SuppressWarnings(PHPMD.UnusedPrivateMethod) */
    private function filemtime(string $filename): string
    {
        return (string) filemtime($filename);
    }

    /**
     * @return list<string>
     *
     * @psalm-assert non-empty-string $regex
     */
    private function getFiles(string $path, string $regex): array
    {
        $iteratorPath = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $rdi = new RecursiveDirectoryIterator(
            $iteratorPath,
            FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS,
        );
        $rdiIterator = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

        /** @var list<string> $files */
        $files = [];
        foreach ($rdiIterator as $key => $fileInfo) {
            assert(is_string($key) && $key !== '');
            assert($fileInfo instanceof SplFileInfo);
            /** @var non-empty-string $normalizedFileName */
            $normalizedFileName = str_replace('\\', '/', $key);
            assert($regex !== '');
            if (! preg_match($regex, $normalizedFileName)) {
                continue;
            }

            if (! $fileInfo->isFile()) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $files[] = $normalizedFileName;
        }

        return $files;
    }
}
