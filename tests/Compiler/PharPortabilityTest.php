<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

use function array_merge;
use function assert;
use function BEAR\Package\deleteFiles;
use function copy;
use function dirname;
use function escapeshellarg;
use function fclose;
use function file_put_contents;
use function getenv;
use function is_file;
use function is_resource;
use function json_encode;
use function mkdir;
use function proc_close;
use function proc_open;
use function rmdir;
use function sprintf;
use function str_ends_with;
use function stream_get_contents;

use const JSON_PRETTY_PRINT;
use const PHP_BINARY;
use const PHP_EOL;

/**
 * What .github/workflows/phar.yml checks with grep: an archive Compiler::phar() packs
 * boots from another directory with the build tree deleted, and boots again under
 * another temp directory.
 *
 * The archive is built from a self-contained application assembled under tests/tmp:
 * the repository's vendor, with this checkout's sources where a composer install of
 * bear/package would put them. tests/Fake/fake-app cannot serve - its autoloader is a
 * shim onto the repository, so an archive packed from it is not portable.
 *
 * Built once per run, keyed on existence: tests/bootstrap.php empties tests/tmp at
 * every process start, so an archive that exists belongs to this run.
 */
class PharPortabilityTest extends TestCase
{
    private const CONTEXT = 'prod-cli-hal-app';
    private const APP_NAME = 'FakeVendor\PharApp';

    public function testBootsFromAnotherDirectoryWithTheBuildTreeDeleted(): void
    {
        $paths = self::builtArchive();
        deleteFiles($paths['app']);
        @rmdir($paths['app']);
        $this->assertDirectoryDoesNotExist($paths['app']);

        [$code, $output] = self::boot($paths['phar']);

        $this->assertSame(0, $code, $output);
        $this->assertStringContainsString('200 OK', $output);
        $this->assertStringContainsString('Hello BEAR.Sunday', $output);
    }

    /** The archive names no directory to write in, so the machine that boots answers it. */
    public function testBootsUnderAnotherTempDirectory(): void
    {
        $paths = self::builtArchive();
        $elsewhere = dirname(__DIR__) . '/tmp/phar/elsewhere';
        @mkdir($elsewhere, 0777, true);

        [$code, $output] = self::boot($paths['phar'], $elsewhere);

        $this->assertSame(0, $code, $output);
        $this->assertStringContainsString('200 OK', $output);
    }

    /** @return array{app: string, phar: string} */
    private static function builtArchive(): array
    {
        $base = dirname(__DIR__) . '/tmp/phar';
        $paths = [
            'app' => $base . '/app',
            'phar' => $base . '/moved/app.phar',
        ];
        if (is_file($paths['phar'])) {
            return $paths;
        }

        self::assemble($paths['app']);
        self::compile($paths['app']);
        @mkdir(dirname($paths['phar']), 0777, true);
        if (! @copy($paths['app'] . '/app.phar', $paths['phar'])) {
            throw new RuntimeException('the compile left no app.phar to move');
        }

        return $paths;
    }

    /**
     * The copied autoloader maps BEAR\Package onto the repository root and requires
     * its dev files - neither ships in an archive - so dump-autoload writes one for
     * the new root from the fixture's own composer.json.
     */
    private static function assemble(string $app): void
    {
        $root = dirname(__DIR__, 2);
        self::copyDir($root . '/vendor', $app . '/vendor');
        self::copyDir($root . '/src', $app . '/vendor/bear/package/src');
        self::copyDir($root . '/src-deprecated', $app . '/vendor/bear/package/src-deprecated');
        self::copyDir($root . '/bin', $app . '/vendor/bear/package/bin');
        self::appFile($app . '/src/Module/AppModule.php', self::appModule());
        self::appFile($app . '/src/Module/ProdModule.php', self::prodModule());
        self::appFile($app . '/src/Module/App.php', self::app());
        self::appFile($app . '/src/Resource/Page/Index.php', self::index());
        self::appFile($app . '/public/index.php', self::entry());
        self::appFile($app . '/bin/compile.php', self::compileScript());
        self::appFile($app . '/composer.json', (string) json_encode([
            'name' => 'fake-vendor/phar-app',
            'require' => ['php' => '^8.2'],
            'autoload' => [
                'psr-4' => [
                    self::APP_NAME . '\\' => 'src/',
                    'BEAR\\Package\\' => ['vendor/bear/package/src/', 'vendor/bear/package/src-deprecated/'],
                ],
            ],
        ], JSON_PRETTY_PRINT));

        [$code, $output] = self::spawn(self::composerCommand() . ' dump-autoload --no-interaction --no-plugins --no-scripts', $app);
        if ($code !== 0) {
            throw new RuntimeException(sprintf('composer dump-autoload failed (%d):%s%s', $code, PHP_EOL, $output));
        }
    }

    private static function compile(string $app): void
    {
        $command = sprintf('%s -d memory_limit=-1 %s', escapeshellarg(PHP_BINARY), escapeshellarg($app . '/bin/compile.php'));
        [$code, $output] = self::spawn($command, $app, ['CONTEXT' => self::CONTEXT]);
        if ($code !== 0) {
            throw new RuntimeException(sprintf('compile and pack failed (%d):%s%s', $code, PHP_EOL, $output));
        }
    }

    /** @return array{int, string} exit code and merged output of one boot */
    private static function boot(string $phar, string|null $elsewhere = null): array
    {
        $command = sprintf('%s %s get /index', escapeshellarg(PHP_BINARY), escapeshellarg($phar));
        $env = ['CONTEXT' => self::CONTEXT];
        if ($elsewhere !== null) {
            $env['TMPDIR'] = $elsewhere;
        }

        return self::spawn($command, dirname($phar), $env);
    }

    /**
     * @param array<string, string> $env added to the current environment
     *
     * @return array{int, string} exit code and merged stdout/stderr
     */
    private static function spawn(string $command, string $cwd, array $env = []): array
    {
        $pipes = [];
        $process = proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd,
            $env === [] ? null : array_merge(getenv(), $env),
        );
        assert(is_resource($process));
        $output = (string) stream_get_contents($pipes[1]) . (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($process);

        return [$code, $output];
    }

    /** The COMPOSER_BINARY composer exposes to its scripts, or whatever the PATH resolves. */
    private static function composerCommand(): string
    {
        $composer = getenv('COMPOSER_BINARY');
        if ($composer === false || $composer === '') {
            return 'composer';
        }

        return str_ends_with($composer, '.phar')
            ? escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($composer)
            : escapeshellarg($composer);
    }

    private static function copyDir(string $from, string $to): void
    {
        @mkdir($to, 0777, true);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $target = $to . '/' . $iterator->getSubPathname();
            if ($file->isDir()) {
                @mkdir($target, 0777, true);
                continue;
            }

            if (! @copy($file->getPathname(), $target)) {
                throw new RuntimeException(sprintf('could not copy %s', $file->getPathname()));
            }
        }
    }

    private static function appFile(string $path, string $contents): void
    {
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, $contents);
    }

    private static function appModule(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace FakeVendor\PharApp\Module;

            use BEAR\Package\PackageModule;
            use Ray\Di\AbstractModule;

            class AppModule extends AbstractModule
            {
                protected function configure(): void
                {
                    $this->install(new PackageModule());
                }
            }
            PHP;
    }

    /**
     * ReadOnlyAppModule with neither directory named: the machine that boots answers both,
     * so the archive carries no path of the build machine's and writes nowhere inside itself.
     */
    private static function prodModule(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace FakeVendor\PharApp\Module;

            use BEAR\Package\Context\ProdModule as PackageProdModule;
            use BEAR\Package\Module\ReadOnlyAppModule;
            use Ray\Di\AbstractModule;

            class ProdModule extends AbstractModule
            {
                protected function configure(): void
                {
                    $this->install(new ReadOnlyAppModule());
                    $this->install(new PackageProdModule());
                }
            }
            PHP;
    }

    private static function app(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace FakeVendor\PharApp\Module;

            use BEAR\Resource\ResourceInterface;
            use BEAR\Sunday\Extension\Application\AppInterface;
            use BEAR\Sunday\Extension\Error\ThrowableHandlerInterface;
            use BEAR\Sunday\Extension\Router\RouterInterface;
            use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
            use BEAR\Sunday\Extension\Transfer\TransferInterface;

            final class App implements AppInterface
            {
                public function __construct(
                    public readonly HttpCacheInterface $httpCache,
                    public readonly RouterInterface $router,
                    public readonly TransferInterface $responder,
                    public readonly ResourceInterface $resource,
                    public readonly ThrowableHandlerInterface $throwableHandler
                ) {
                }
            }
            PHP;
    }

    private static function index(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace FakeVendor\PharApp\Resource\Page;

            use BEAR\Resource\ResourceObject;

            class Index extends ResourceObject
            {
                public function onGet(string $name = 'BEAR.Sunday'): static
                {
                    $this->body = [
                        'greeting' => 'Hello ' . $name
                    ];

                    return $this;
                }
            }
            PHP;
    }

    private static function entry(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace FakeVendor\PharApp;

            use BEAR\Package\Injector;
            use BEAR\Resource\Method;
            use BEAR\Sunday\Extension\Application\AppInterface;
            use BEAR\Sunday\Extension\Router\NullMatch;
            use FakeVendor\PharApp\Module\App;

            require dirname(__DIR__) . '/vendor/autoload.php';

            $context = getenv('CONTEXT') ?: (PHP_SAPI === 'cli' ? 'cli-hal-app' : 'hal-app');

            $app = Injector::getInstance('FakeVendor\PharApp', $context, dirname(__DIR__))->getInstance(AppInterface::class);
            assert($app instanceof App);
            // match() throws BadRequestException on client input it cannot read.
            $request = new NullMatch();
            try {
                $request = $app->router->match($GLOBALS, $_SERVER);
                $app->resource->newRequest(
                    Method::from($request->method), $request->path, $request->query
                )()->transfer($app->responder, $_SERVER);
                exit(0);
            } catch (\Throwable $e) {
                $app->throwableHandler->handle($e, $request)->transfer();
                exit(1);
            }
            PHP;
    }

    private static function compileScript(): string
    {
        return <<<'PHP'
            <?php

            declare(strict_types=1);

            use BEAR\Package\Compiler;

            require dirname(__DIR__) . '/vendor/autoload.php';

            ini_set('memory_limit', '-1');

            $context = getenv('CONTEXT') ?: 'prod-cli-hal-app';

            $compiler = new Compiler('FakeVendor\PharApp', $context, dirname(__DIR__));
            $code = $compiler();
            exit($code === 0 ? $compiler->phar() : $code);
            PHP;
    }
}
