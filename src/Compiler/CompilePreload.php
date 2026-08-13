<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Types;

use function array_filter;
use function array_values;
use function assert;
use function class_exists;
use function get_included_files;
use function realpath;
use function sprintf;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;

/**
 * @psalm-import-type AppDir from Types
 * @psalm-import-type ClassList from Types
 * @psalm-import-type Context from Types
 */
final class CompilePreload
{
    /** @param ClassList $classes */
    public function __construct(
        private FakeRun $fakeRun,
        private CompileAutoload $dumpAutoload,
        private FilePutContents $filePutContents,
        private ArrayObject $classes,
        private PreloadClassFilter $isPreloadClass,
    ) {
    }

    /**
     * @param Context $context
     *
     * @return non-empty-string
     */
    public function __invoke(AbstractAppMeta $appMeta, string $context): string
    {
        ($this->fakeRun)();
        $this->loadResources($appMeta);
        // The tracker records a class after its load returns, so dependencies are
        // appended first: the list is in dependency order and plain require is safe.
        /** @var list<string> $trackedClasses */
        $trackedClasses = (array) $this->classes;
        $classes = array_values(array_filter($trackedClasses, $this->isPreloadClass));

        $paths = $this->dumpAutoload->getPaths($classes);
        $requiredFile = '';
        foreach ($paths as $path) {
            $requiredFile .= sprintf(
                "require %s;\n",
                $path,
            );
        }

        $preloadFile = sprintf("<?php

// %s preload
// A one-shot process pays for this list and throws it away, and php://stdout cannot even be
// opened this early under the CLI SAPI - a dependency that touches it there takes the process
// down with no message. Preload where the process is reused.
if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    return;
}

require __DIR__ . '/vendor/autoload.php';
%s%s", $context, $requiredFile, $this->compileScripts($appMeta, $context));
        $appDirRealpath = realpath($appMeta->appDir);
        assert($appDirRealpath !== false);
        $fileName = $appDirRealpath . '/preload.php';
        ($this->filePutContents)($fileName, $preloadFile);

        return $fileName;
    }

    /**
     * Compile the DI scripts and AOP proxies this boot loaded, without running them.
     *
     * They cannot be required: a DI script builds an instance from variables that only exist
     * in the injector's scope. opcache_compile_file() stores the opcodes and skips execution,
     * and preload links what it compiled after the whole file has run, so order is free.
     *
     * The list is what the boot loaded, never a glob of the script directory: a proxy whose
     * parent this boot never loaded cannot be linked, and PHP would say so on every startup.
     *
     * @param Context $context
     */
    private function compileScripts(AbstractAppMeta $appMeta, string $context): string
    {
        /** @var AppDir $appDir */
        $appDir = $appMeta->appDir;
        $scriptDir = realpath(AppDirs::script($appDir, $context));
        if ($scriptDir === false) {
            return ''; // @codeCoverageIgnore
        }

        $scripts = [];
        foreach (get_included_files() as $file) {
            if (! str_starts_with($file, $scriptDir . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $scripts[] = $file;
        }

        if ($scripts === []) {
            return ''; // @codeCoverageIgnore
        }

        // function_exists() is not enough: with opcache disabled the call only warns.
        $block = "\nif (function_exists('opcache_compile_file') && ini_get('opcache.preload')) {\n";
        foreach ($this->dumpAutoload->getFilePaths($scripts) as $path) {
            $block .= sprintf("    opcache_compile_file(%s);\n", $path);
        }

        return $block . "}\n";
    }

    /**
     * Load every resource class without constructing it.
     *
     * A resource can need request state to build - tests/Fake/fake-app's AuthProvider throws
     * unless a user is logged in - and a boot only constructs what it serves. Preload records
     * classes, so loading the file is the whole job here.
     */
    public function loadResources(AbstractAppMeta $appMeta): void
    {
        foreach ($appMeta->getGenerator('*') as $resMeta) {
            class_exists($resMeta->class);
        }
    }
}
