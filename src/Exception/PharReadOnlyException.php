<?php

declare(strict_types=1);

namespace BEAR\Package\Exception;

final class PharReadOnlyException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('phar.readonly is enabled and cannot be changed at run time; spawn the builder with php -d phar.readonly=0.');
    }
}
