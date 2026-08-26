<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use PHPUnit\Framework\TestCase;

use function str_replace;
use function sys_get_temp_dir;

class AppMetaProviderTest extends TestCase
{
    public function testTwoTreesOfOneApplicationAnswerDifferentTempDirs(): void
    {
        $first = $this->machineTempMeta(sys_get_temp_dir() . '/bear-tree-a');
        $second = $this->machineTempMeta(sys_get_temp_dir() . '/bear-tree-b');

        $this->assertNotSame($first->tmpDir, $second->tmpDir);
        $this->assertNotSame($first->logDir, $second->logDir);
    }

    public function testTheMachineTempKeepsTheShapeTheTreeUses(): void
    {
        $meta = $this->machineTempMeta(sys_get_temp_dir() . '/bear-tree-a');

        $this->assertStringStartsWith(str_replace('\\', '/', sys_get_temp_dir()) . '/', $meta->tmpDir);
        $this->assertStringEndsWith('/var/tmp/prod-app', $meta->tmpDir);
        $this->assertStringEndsWith('/var/log/prod-app', $meta->logDir);
    }

    public function testADeclaredDirectoryIsAnsweredAsGiven(): void
    {
        $compiled = new Meta('FakeVendor\HelloWorld', 'prod-app', sys_get_temp_dir() . '/bear-tree-a');
        $declared = new WriteDirs('/data/shop/tmp', '/data/shop/log');

        $meta = (new AppMetaProvider($compiled, $declared, $this->shape()))->get();

        $this->assertSame('/data/shop/tmp', $meta->tmpDir);
        $this->assertSame('/data/shop/log', $meta->logDir);
    }

    private function machineTempMeta(string $appDir): AbstractAppMeta
    {
        $compiled = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);

        return (new AppMetaProvider($compiled, new WriteDirs(), $this->shape()))->get();
    }

    private function shape(): WriteShape
    {
        return new WriteShape('var/tmp/prod-app', 'var/log/prod-app');
    }
}
