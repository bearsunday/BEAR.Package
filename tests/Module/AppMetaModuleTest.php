<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\CompileSteps;
use BEAR\Package\Module;
use PHPUnit\Framework\TestCase;
use Ray\Di\Name;

use function dirname;

class AppMetaModuleTest extends TestCase
{
    public function testCompileStepsIsExplicitlyBound(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $container = (new Module())($meta, 'app')->getContainer()->getContainer();

        $this->assertArrayHasKey(CompileSteps::class . '-' . Name::ANY, $container);
    }
}
