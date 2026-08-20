<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use PHPUnit\Framework\TestCase;

use function realpath;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;

final class WriteBaseTest extends TestCase
{
    public function testDefaultLayoutHasNoBase(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app');
        $this->assertNull(WriteBase::of($meta));
    }

    public function testWriteDirLayoutYieldsTheBase(): void
    {
        $base = sys_get_temp_dir() . '/bear-write-base-' . uniqid();
        $meta = Meta::create('FakeVendor\HelloWorld', 'prod-app', Meta::appDir('FakeVendor\HelloWorld'), $base);
        $this->assertSame(str_replace('\\', '/', (string) realpath($base)), WriteBase::of($meta));
    }
}
