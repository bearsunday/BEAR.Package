<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use Override;
use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

use function str_replace;
use function sys_get_temp_dir;

/**
 * The one hook a compiled container runs at boot, which is the only place a machine can answer.
 *
 * The template carries a relative directory wherever the declaration left one out. Nothing else
 * is passed: the application and the context the key names are already spelled into that path,
 * as Meta spells them into its own.
 *
 * @implements ProviderInterface<AbstractAppMeta>
 */
final class AppMetaProvider implements ProviderInterface
{
    public const TEMPLATE = 'bear_package_write_template';

    public function __construct(
        #[Named(self::TEMPLATE)]
        private AbstractAppMeta $template,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(): AbstractAppMeta
    {
        $meta = clone $this->template;
        $meta->tmpDir = self::resolved($meta->tmpDir);
        $meta->logDir = self::resolved($meta->logDir);

        return $meta;
    }

    /**
     * @param non-empty-string $dir
     *
     * @return non-empty-string
     */
    private static function resolved(string $dir): string
    {
        if (WriteDirs::isAbsolute($dir)) {
            return $dir;
        }

        return str_replace('\\', '/', sys_get_temp_dir()) . '/' . $dir;
    }
}
