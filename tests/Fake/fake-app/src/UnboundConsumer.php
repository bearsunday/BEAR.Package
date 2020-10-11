<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld;

class UnboundConsumer
{
    /**
     * UnboundInterface is not bound
     */
    public function __construct(\BEAR\Resource\UnboundInterface $unbound)
    {
    }
}
