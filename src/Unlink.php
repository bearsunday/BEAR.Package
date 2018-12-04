<?php

declare(strict_types=1);

namespace BEAR\Package;

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

    public function __invoke(string $path)
    {
        if ($this->isOptional && file_exists($path . '/.do_not_clear')) {
            return;
        }
        foreach (\glob(\rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
            \is_dir($file) ? $this->__invoke($file) : \unlink($file);
            @\rmdir($file);
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
        ($this)($path);

        return true;
    }
}
