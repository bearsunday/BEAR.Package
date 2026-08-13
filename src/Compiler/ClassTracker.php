<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Exception\ComposerLoaderNotFoundException;
use BEAR\Package\Types;
use Composer\Autoload\ClassLoader;

use function assert;
use function file_exists;
use function is_array;
use function spl_autoload_functions;
use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 * Records the classes a process autoloads, in dependency order.
 *
 * The tracker takes Composer's place in the autoload queue and appends a class
 * after its load returns, so the classes it depends on are already in the list:
 * a plain `require` list built from it runs top to bottom.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type ClassList from Types
 */
final class ClassTracker
{
    /** @var ClassList */
    private ArrayObject $classes;
    private PreloadClassFilter $filter;

    private function __construct(private ClassLoader $loader)
    {
        /** @var ClassList $classes */
        $classes = new ArrayObject();
        $this->classes = $classes;
        $this->filter = new PreloadClassFilter($loader);
    }

    /**
     * @param AppDir $appDir
     *
     * @throws ComposerLoaderNotFoundException
     */
    public static function fromAppDir(string $appDir): self
    {
        $loaderFile = $appDir . '/vendor/autoload.php';
        if (! file_exists($loaderFile)) {
            throw new ComposerLoaderNotFoundException($appDir);
        }

        // Keep Composer autoload registered until PreloadClassFilter is constructed:
        // getLoader() will not re-register after unregisterComposerLoader().
        $loader = require $loaderFile;
        assert($loader instanceof ClassLoader);

        return new self($loader);
    }

    public function register(): void
    {
        $this->unregisterComposerLoader();
        spl_autoload_register(
            function (string $class): void {
                $this->loader->loadClass($class);
                if ($this->filter->isExcludedClass($class)) {
                    return;
                }

                $this->classes->append($class);
            },
            true,
            true,
        );
    }

    /** @return ClassList */
    public function classes(): ArrayObject
    {
        return $this->classes;
    }

    public function filter(): PreloadClassFilter
    {
        return $this->filter;
    }

    /**
     * Unregister this loader, not whatever sits first.
     *
     * Index 0 is not always Composer: a second tracker in the same process would find the
     * first tracker's closure there, and a package whose autoload files prepend a loader of
     * their own would lose it - silently, mid-compile.
     */
    private function unregisterComposerLoader(): void
    {
        foreach (spl_autoload_functions() as $autoload) {
            if (! is_array($autoload) || $autoload[0] !== $this->loader) {
                continue;
            }

            spl_autoload_unregister($autoload);

            return;
        }
    }
}
