<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use PHPUnit\Framework\TestCase;

use function dirname;
use function touch;

class BindingsUpdateTest extends TestCase
{
    public function testBindingUpdated(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app');
        $bindingsUpdate = new FileUpdate($meta);
        $isUpdated = $bindingsUpdate->isUpdated($meta);
        $this->assertFalse($isUpdated);

        touch(dirname(__DIR__) . '/Fake/fake-app/src/Module/AppModule.php');
        $isUpdated = $bindingsUpdate->isUpdated($meta);
        $this->assertTrue($isUpdated);
    }
}
