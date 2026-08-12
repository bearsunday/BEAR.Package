<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Compiler;
use BEAR\Package\Context\CliModule;
use BEAR\Package\Provide\Error\NullPage;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

use function assert;
use function BEAR\Package\createAnonymousForPreloadFilterTest;
use function class_exists;
use function dirname;
use function file_put_contents;
use function is_array;
use function is_file;
use function realpath;
use function reset;
use function spl_autoload_functions;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

require_once dirname(__DIR__) . '/Fake/preload/anonymous.php';

class PreloadClassFilterTest extends TestCase
{
    private PreloadClassFilter $filter;
    private ClassLoader $loader;

    protected function setUp(): void
    {
        $this->loader = $this->composerLoader();
        $this->filter = new PreloadClassFilter($this->loader);
    }

    public function testRejectsExcludedAndUndeclaredSymbols(): void
    {
        $this->assertFalse(($this->filter)(NullPage::class));
        $this->assertFalse(($this->filter)(Compiler::class));
        $this->assertFalse(($this->filter)('BEAR\\Package\\DefinitelyNotAClass' . self::class));
    }

    public function testRejectsAnonymousClasses(): void
    {
        // Create outside BEAR\Package\Compiler\* so isExcludedClass does not short-circuit first.
        $anonymous = createAnonymousForPreloadFilterTest();
        $this->assertFalse(($this->filter)($anonymous::class));
    }

    public function testAcceptsNormalDeclaredClass(): void
    {
        $this->assertTrue(class_exists(CliModule::class));
        $this->assertTrue(($this->filter)(CliModule::class));
    }

    public function testRejectsWhenDeclarationFileDiffersFromAutoloadMap(): void
    {
        $tmp = sys_get_temp_dir() . '/bear-preload-shadow-' . uniqid('', true) . '.php';
        $class = 'BearPackageShadow' . uniqid();
        file_put_contents($tmp, "<?php class {$class} {}");
        require $tmp;
        $this->assertTrue(class_exists($class, false));

        // ClassLoader has no map entry (or a different path); findFile is false → reject.
        $this->assertFalse(($this->filter)($class));
        @unlink($tmp);
    }

    public function testRejectsComposerAutoloadFilesEntry(): void
    {
        // Files from Composer's "files" autoload are already required by vendor/autoload.php.
        $composerDir = dirname((string) (new ReflectionClass(ClassLoader::class))->getFileName());
        $autoloadFiles = $composerDir . '/autoload_files.php';
        if (! is_file($autoloadFiles)) {
            $this->markTestSkipped('autoload_files.php not present');
        }

        /** @var array<string, string> $files */
        $files = require $autoloadFiles;
        if ($files === []) {
            $this->markTestSkipped('no composer files autoload entries');
        }

        $path = (string) realpath(reset($files));
        // ClassLoader itself lives under vendor/composer.
        $this->assertFalse(($this->filter)(ClassLoader::class));
        $this->assertNotSame('', $path);
    }

    public function testNormalizePathKeepsPharUri(): void
    {
        $normalize = new ReflectionMethod(PreloadClassFilter::class, 'normalizePath');
        $result = $normalize->invoke($this->filter, 'phar:///tmp/app.phar/src/Foo.php');
        $this->assertSame('phar:///tmp/app.phar/src/Foo.php', $result);
    }

    private function composerLoader(): ClassLoader
    {
        $autoloads = spl_autoload_functions();
        assert($autoloads !== []);
        foreach ($autoloads as $autoload) {
            if (is_array($autoload) && $autoload[0] instanceof ClassLoader) {
                return $autoload[0];
            }
        }

        $loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
        assert($loader instanceof ClassLoader);

        return $loader;
    }
}
