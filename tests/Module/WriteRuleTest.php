<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use PHPUnit\Framework\TestCase;

use function dirname;
use function serialize;
use function str_replace;
use function sys_get_temp_dir;
use function unserialize;

class WriteRuleTest extends TestCase
{
    private const APP = 'FakeVendor\\HelloWorld';
    private const CONTEXT = 'prod-app';

    /** Meta and WriteRule both spell a resolved directory forward-slashed. */
    private static function slashed(string $dir): string
    {
        return str_replace('\\', '/', $dir);
    }

    /** Declaring nothing keeps an application in its own tree, where a boot resolves the root. */
    public function testNoDeclaration(): void
    {
        $rule = new WriteRule(self::APP, self::CONTEXT);
        $appDir = self::slashed(dirname(__DIR__)) . '/Fake/fake-app';

        $this->assertSame($appDir . '/var/tmp/prod-app', $rule->tmpDir());
        $this->assertSame($appDir . '/var/log/prod-app', $rule->logDir());
    }

    /** A named directory is the declaration's to give, and nothing resolves it again. */
    public function testFullDeclaration(): void
    {
        $rule = new WriteRule(self::APP, self::CONTEXT, new WriteDirs('/var/tmp/named', '/var/log/named'));

        $this->assertSame('/var/tmp/named', $rule->tmpDir());
        $this->assertSame('/var/log/named', $rule->logDir());
        $this->assertFalse($rule->needsBoot());
    }

    /** Omitting both asks the machine, under a directory named for the application. */
    public function testOmittedBoth(): void
    {
        $rule = new WriteRule(self::APP, self::CONTEXT, new WriteDirs(null, null));
        $base = self::slashed(sys_get_temp_dir()) . '/FakeVendor/HelloWorld/prod-app';

        $this->assertSame($base . '/tmp', $rule->tmpDir());
        $this->assertSame($base . '/log', $rule->logDir());
        $this->assertTrue($rule->needsBoot());
    }

    /** One of the two can be named on its own. */
    public function testOneOmitted(): void
    {
        $rule = new WriteRule(self::APP, self::CONTEXT, new WriteDirs(null, '/var/log/named'));

        $this->assertSame(self::slashed(sys_get_temp_dir()) . '/FakeVendor/HelloWorld/prod-app/tmp', $rule->tmpDir());
        $this->assertSame('/var/log/named', $rule->logDir());
        $this->assertTrue($rule->needsBoot());
    }

    /** An application nested under another hangs off whatever the parent answers. */
    public function testNestedUnderANamedParent(): void
    {
        $parent = new WriteRule('Host\App', 'prod-app', new WriteDirs('/var/tmp/host', '/var/log/host'));
        $rule = new WriteRule('Guest\App', 'app', null, $parent);

        $this->assertSame('/var/tmp/host/Guest/App/app/tmp', $rule->tmpDir());
        $this->assertSame('/var/log/host/Guest/App/app/log', $rule->logDir());
    }

    /** The parent's own answer is asked at the same moment, so a machine's reaches the nested one. */
    public function testNestedUnderAParentThatAsksTheMachine(): void
    {
        $parent = new WriteRule('Host\App', 'prod-app', new WriteDirs(null, null));
        $rule = new WriteRule('Guest\App', 'app', null, $parent);

        $this->assertSame(self::slashed(sys_get_temp_dir()) . '/Host/App/prod-app/tmp/Guest/App/app/tmp', $rule->tmpDir());
        $this->assertTrue($rule->needsBoot());
    }

    /** A nested application that names its own directories keeps them. */
    public function testNestedWithItsOwnDeclaration(): void
    {
        $parent = new WriteRule('Host\App', 'prod-app', new WriteDirs('/var/tmp/host', '/var/log/host'));
        $rule = new WriteRule('Guest\App', 'app', new WriteDirs('/var/tmp/guest', '/var/log/guest'), $parent);

        $this->assertSame('/var/tmp/guest', $rule->tmpDir());
        $this->assertSame('/var/log/guest', $rule->logDir());
    }

    /**
     * A rule is what a compiled container holds, so nothing a machine answered can be in it -
     * this is the whole reason the directories are not resolved while compiling.
     */
    public function testWhatACompiledContainerCarries(): void
    {
        $parent = new WriteRule('Host\App', 'prod-app', new WriteDirs(null, null));
        $rule = new WriteRule(self::APP, self::CONTEXT, null, $parent);

        $this->assertStringNotContainsString(self::slashed(sys_get_temp_dir()), serialize($rule));
        $restored = unserialize(serialize($rule));
        $this->assertInstanceOf(WriteRule::class, $restored);
        $this->assertSame($rule->tmpDir(), $restored->tmpDir());
    }
}
