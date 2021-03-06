<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\ResourceObject;

/**
 * @Cacheable
 */
class NullPage extends ResourceObject
{
    public function onGet(string $required, int $optional = 0): ResourceObject
    {
        return $this;
    }
}
