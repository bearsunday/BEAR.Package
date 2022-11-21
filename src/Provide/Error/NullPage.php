<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\RepositoryModule\Annotation\Cacheable;
use BEAR\Resource\NullRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Inject;

#[Cacheable]
class NullPage extends ResourceObject
{
    #[Inject(optional: true)]
    public function setRenderer(RenderInterface $renderer): self
    {
        unset($renderer);
        $this->renderer = new NullRenderer();

        return $this;
    }

    public function onGet(string $required, int $optional = 0): ResourceObject
    {
        unset($required, $optional);

        return $this;
    }
}
