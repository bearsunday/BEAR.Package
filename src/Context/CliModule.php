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
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Name;

use function crc32;
use function sys_get_temp_dir;
use function tempnam;

final class CliModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        // Ray.Di 2.21+ defers rename() until after configure()+merge, so rename-then-bind
        // in the same configure() renames the newly bound CliRouter. Move the parent
        // binding on lastModule first (2.20 rename semantics), then bind CliRouter.
        /** @psalm-suppress DeprecatedProperty lastModule is the chained parent module */
        if ($this->lastModule instanceof AbstractModule) {
            $this->lastModule->getContainer()->move(
                RouterInterface::class,
                Name::ANY,
                RouterInterface::class,
                'original',
            );
        }

        $this->bind(RouterInterface::class)->to(CliRouter::class);
        $this->bind(TransferInterface::class)->to(CliResponder::class);
        $this->bind(HttpCacheInterface::class)->to(CliHttpCache::class);
        $stdIn = tempnam(sys_get_temp_dir(), 'stdin-' . crc32(__FILE__));
        $this->bind()->annotatedWith(StdIn::class)->toInstance($stdIn);
    }
}
