<?php

declare(strict_types=1);

namespace BEAR\Package;

use function is_dir;
use function rtrim;
use function unlink;

final class Unlink
{
    /**
     * @var array
     */
    private static $unlinkedPath = [];

    /**
     * @var bool
     */
    private $isOptional = true;

    public function __invoke(string $path) : void
    {
        if ($this->isOptional && file_exists($path . '/.do_not_clear')) {
            return;
        }
        foreach ((array) glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
            is_dir((string) $file) ? $this->__invoke((string) $file) : unlink((string) $file);
            @rmdir((string) $file);
        }
    }

    public function once(string $path) : bool
    {
        if (in_array($path, self::$unlinkedPath, true)) {
            return true;
        }
        self::$unlinkedPath[] = $path;
        $this->__invoke($path);

        return false;
    }

    public function force(string $path) : bool
    {
        $this->isOptional = false;
        $this($path);

        return true;
    }
}
