<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

use function sprintf;

/**
 * The application directory has no Composer autoloader.
 *
 * The compiler takes Composer's place in the autoload queue to record what loads; without
 * `vendor/autoload.php` there is nothing to take over, and the directory is not an installed
 * application.
 */
final class ComposerLoaderNotFoundException extends RuntimeException
{
    public function __construct(string $appDir)
    {
        parent::__construct(sprintf('No Composer autoloader in "%s": run composer install.', $appDir));
    }
}
