<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use PHPUnit\Framework\TestCase;

use function dirname;
use function touch;

class FileUpdateTest extends TestCase
{
    public function testBindingUpdated(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app');
        $bindingsUpdate = new FileUpdate($meta);
        $isUpdated = $bindingsUpdate->isNotUpdated($meta);
        $this->assertTrue($isUpdated);

        touch(dirname(__DIR__) . '/Fake/fake-app/src/Module/AppModule.php');
        $isUpdated = $bindingsUpdate->isNotUpdated($meta);
        $this->assertFalse($isUpdated);
    }
}
