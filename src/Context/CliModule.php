<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Annotation\StdIn;
use BEAR\Package\Provide\Router\CliRouter;
use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\QueryRepository\CliHttpCache;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Di\AbstractModule;

class CliModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->rename(RouterInterface::class, 'original');
        $this->bind(RouterInterface::class)->to(CliRouter::class);
        $this->bind(TransferInterface::class)->to(CliResponder::class);
        $this->bind(HttpCacheInterface::class)->to(CliHttpCache::class);
        $stdIn = tempnam(sys_get_temp_dir(), 'stdin-' . crc32(__FILE__));
        $this->bind()->annotatedWith(StdIn::class)->toInstance($stdIn);
    }
}
