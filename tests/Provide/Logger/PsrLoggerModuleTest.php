<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\AppMetaModule;
use BEAR\Package\Context\ProdModule;
use BEAR\Package\Module\WriteModule;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ray\Di\Injector;

class PsrLoggerModuleTest extends TestCase
{
    public function testPsrLoggerModule(): void
    {
        $logger = (new Injector(new PsrLoggerModule(self::appMetaModule())))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testProdPsrLoggerModule(): void
    {
        $logger = (new Injector(new ProdModule(new PsrLoggerModule(self::appMetaModule()))))->getInstance(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    /** AppMetaModule resolves the Meta through the module tree, which Module::__invoke seeds. */
    private static function appMetaModule(): AppMetaModule
    {
        $meta = new AppMeta('FakeVendor\HelloWorld');

        return new AppMetaModule($meta, new WriteModule($meta, 'app'));
    }
}
