<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Injector;

use function realpath;
use function sprintf;

final class CompilePreload
{
    /** @param ArrayObject<int, string> $classes */
    public function __construct(
        private FakeRun $fakeRun,
        private CompileAutoload $dumpAutoload,
        private FilePutContents $filePutContents,
        private ArrayObject $classes,
        private string $context,
    ) {
        $this->fakeRun = $fakeRun;
    }

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
        $fileName = realpath($appMeta->appDir) . '/preload.php';
        ($this->filePutContents)($fileName, $preloadFile);

        return $fileName;
    }

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
