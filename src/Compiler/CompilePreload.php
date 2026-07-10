<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Types;
use Ray\Di\InjectorInterface;

use function assert;
use function realpath;
use function sprintf;

/**
 * @psalm-import-type ClassList from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppName from Types
 * @psalm-import-type AppDir from Types
 */
final class CompilePreload
{
    /** @param ClassList $classes */
    public function __construct(
        private FakeRun $fakeRun,
        private CompileAutoload $dumpAutoload,
        private FilePutContents $filePutContents,
        private ArrayObject $classes,
        private InjectorInterface $injector,
    ) {
        $this->fakeRun = $fakeRun;
    }

    /** @param Context $context */
    public function __invoke(AbstractAppMeta $appMeta, string $context): string
    {
        ($this->fakeRun)();
        $this->loadResources($appMeta);
        /** @var list<string> $classes */
        $classes = (array) $this->classes;
        $paths = $this->dumpAutoload->getPaths($classes);
        $requiredOnceFile = '';
        foreach ($paths as $path) {
            $requiredOnceFile .= sprintf(
                "require %s;\n",
                $path,
            );
        }

        $preloadFile = sprintf("<?php

// %s preload
require __DIR__ . '/vendor/autoload.php';

%s", $context, $requiredOnceFile);
        $appDirRealpath = realpath($appMeta->appDir);
        assert($appDirRealpath !== false);
        $fileName = $appDirRealpath . '/preload.php';
        ($this->filePutContents)($fileName, $preloadFile);

        return $fileName;
    }

    public function loadResources(AbstractAppMeta $appMeta): void
    {
        foreach ($appMeta->getGenerator('*') as $resMeta) {
            $this->injector->getInstance($resMeta->class);
        }
    }
}
