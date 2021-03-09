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
    /** @var int */
    public $code = 201; // for no template in HTML

    public function onGet(string $required, int $optional = 0): ResourceObject
    {
        return $this;
    }
}
