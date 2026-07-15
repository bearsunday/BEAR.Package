<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Types;
use Ray\Di\InjectorInterface;

use function assert;
use function get_declared_classes;
use function get_declared_interfaces;
use function get_declared_traits;
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
        private PreloadClassFilter $isPreloadClass,
    ) {
        $this->fakeRun = $fakeRun;
    }

    /** @param Context $context */
    public function __invoke(AbstractAppMeta $appMeta, string $context): string
    {
        ($this->fakeRun)();
        $this->loadResources($appMeta);
        $classes = $this->getClasses();
        $paths = $this->dumpAutoload->getPaths($classes);
        $requiredOnceFile = '';
        foreach ($paths as $path) {
            $requiredOnceFile .= sprintf(
                "require_once %s;\n",
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

    /** @return list<string> */
    private function getClasses(): array
    {
        /** @var list<list<string>> $classGroups */
        $classGroups = [
            get_declared_interfaces(),
            get_declared_traits(),
            get_declared_classes(),
            (array) $this->classes,
        ];
        $classes = [];
        $seen = [];
        foreach ($classGroups as $classGroup) {
            foreach ($classGroup as $class) {
                if (isset($seen[$class])) {
                    continue;
                }

                $seen[$class] = true;
                if (! ($this->isPreloadClass)($class)) {
                    continue;
                }

                $classes[] = $class;
            }
        }

        return $classes;
    }

    public function loadResources(AbstractAppMeta $appMeta): void
    {
        foreach ($appMeta->getGenerator('*') as $resMeta) {
            $this->injector->getInstance($resMeta->class);
        }
    }
}
