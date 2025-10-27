<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;
use BEAR\Package\Types;

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
    /**
     * @param ClassList $classes
     * @param Context   $context
     */
    public function __construct(
        private FakeRun $fakeRun,
        private CompileAutoload $dumpAutoload,
        private FilePutContents $filePutContents,
        private ArrayObject $classes,
        private string $context,
    ) {
        $this->fakeRun = $fakeRun;
    }

    /** @param Context $context */
    public function __invoke(AbstractAppMeta $appMeta, string $context): string
    {
        ($this->fakeRun)();
        $this->loadResources($appMeta->name, $context, $appMeta->appDir);
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

%s", $this->context, $requiredOnceFile);
        $appDirRealpath = realpath($appMeta->appDir);
        assert($appDirRealpath !== false);
        $fileName = $appDirRealpath . '/preload.php';
        ($this->filePutContents)($fileName, $preloadFile);

        return $fileName;
    }

    /**
     * @param AppName $appName
     * @param Context $context
     * @param AppDir  $appDir
     */
    public function loadResources(string $appName, string $context, string $appDir): void
    {
        $meta = new Meta($appName, $context, $appDir);
        $injector = Injector::getInstance($appName, $context, $appDir);

        $resMetas = $meta->getGenerator('*');
        foreach ($resMetas as $resMeta) {
            $injector->getInstance($resMeta->class);
        }
    }
}
