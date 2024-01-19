<?php

declare(strict_types=1);

namespace FakeVendor\MinApp\Resource\Page;

use BEAR\Resource\ResourceObject;
use Psr\Log\LoggerInterface;

class Index extends ResourceObject
{
    public function __construct(public LoggerInterface $logger)
    {
    }
    public function onGet(): static
    {
        return $this;
    }
}
