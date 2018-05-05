<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
function delete_dir($path)
{
    foreach (\glob(\rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
        \is_dir($file) ? delete_dir($file) : \unlink($file);
        @\rmdir($file);
    }
}
