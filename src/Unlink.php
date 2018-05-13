<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

final class Unlink
{
    /**
     * @var array
     */
    private static $unlinkedPath = [];

    public function __invoke(string $path)
    {
        if (file_exists($path . '/.do_not_clear')) {
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
}
