<?php

declare(strict_types=1);

namespace BEAR\Package;

use function array_diff;
use function is_dir;
use function rmdir;
use function scandir;
use function unlink;

function deleteFiles(string $path): void
{
    if (! is_dir($path)) {
        return;
    }

    // scandir (not glob('*')) so dotfiles are removed too — notably the AOT compile
    // marker, which would otherwise survive a clean and short-circuit the next boot.
    // The `.placefolder` dir sentinel is preserved so the empty dirs stay in git.
    foreach (array_diff(scandir($path), ['.', '..', '.placefolder']) as $file) {
        $file = $path . '/' . $file;
        is_dir($file) ? deleteFiles($file) : unlink($file);
        @rmdir($file);
    }
}
