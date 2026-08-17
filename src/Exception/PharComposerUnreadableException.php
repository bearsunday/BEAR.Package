<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

/**
 * The application's composer.json is missing, or its autoload section is in a shape this
 * version cannot read.
 *
 * The archive carries the directories that declaration names, so an unread one would ship a
 * tree whose boot path is missing classes. Fix the manifest and pack again.
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/phar.html#when-the-build-stops
 */
final class PharComposerUnreadableException extends RuntimeException
{
    public function __construct(string $file)
    {
        parent::__construct($file);
    }
}
