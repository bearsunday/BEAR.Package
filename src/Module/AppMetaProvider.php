<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use Override;
use Ray\Di\ProviderInterface;

/**
 * A rule that needs the machine is asked here, in the process that boots.
 *
 * The application directory is left for Meta to resolve: an archive and a moved tree each
 * answer it from where their classes load.
 *
 * @implements ProviderInterface<AbstractAppMeta>
 */
final class AppMetaProvider implements ProviderInterface
{
    public function __construct(private WriteRule $rule)
    {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): AbstractAppMeta
    {
        return new Meta(
            $this->rule->app->name,
            $this->rule->app->context,
            '',
            $this->rule->tmpDir(),
            $this->rule->logDir(),
        );
    }
}
