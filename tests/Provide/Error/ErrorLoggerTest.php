<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Package\FakeLogger;
use BEAR\Package\FakeLogRefWriter;
use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Sunday\Extension\Router\RouterMatch;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function array_keys;

class ErrorLoggerTest extends TestCase
{
    private FakeLogger $logger;
    private FakeLogRefWriter $writer;
    private ErrorLogger $errorLogger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new FakeLogger();
        $this->writer = new FakeLogRefWriter();
        $this->errorLogger = new ErrorLogger($this->logger, $this->writer);
    }

    /**
     * A prod deployment binds a writer that keeps nothing, so the logger is the only place
     * the stack trace survives, and it goes at the level of the error rather than below it.
     */
    public function testSendsTheExceptionRenderingToTheLogger(): void
    {
        $e = new LogicException('msg', 500);

        ($this->errorLogger)($e, self::request());

        $detail = $this->writer->written[(string) new LogRef($e)];
        $this->assertStringContainsString($e->getTraceAsString(), $detail);
        $this->assertContains($detail, $this->logger->messages['error']);
        $this->assertSame([], $this->logger->messages['debug'] ?? []);
    }

    public function testSendsTheSummaryLineToTheLogger(): void
    {
        $e = new LogicException('msg', 500);

        ($this->errorLogger)($e, self::request());

        $this->assertContains(
            'req:"get /" code:500 e:LogicException(msg) logref:' . new LogRef($e),
            $this->logger->messages['error'],
        );
    }

    /** The page reports 503 for any RuntimeException, so the log level matches even with code 0. */
    public function testLogsUncaughtRuntimeExceptionAtError(): void
    {
        $e = new RuntimeException('msg', 0);

        ($this->errorLogger)($e, self::request());

        $this->assertSame([], $this->logger->messages['debug'] ?? []);
        $this->assertCount(2, $this->logger->messages['error']);
        $this->assertArrayHasKey((string) new LogRef($e), $this->writer->written);
    }

    /** Below 500 both lines drop together: a logger filtered to errors keeps neither. */
    #[DataProvider('clientErrorProvider')]
    public function testLogsClientErrorAtDebugWithoutLogRef(Throwable $e): void
    {
        ($this->errorLogger)($e, self::request());

        $this->assertSame([], $this->logger->messages['error'] ?? []);
        $this->assertCount(2, $this->logger->messages['debug']);
        $this->assertStringNotContainsString('logref:', $this->logger->messages['debug'][0]);
        $this->assertSame([], $this->writer->written);
    }

    /** @return array<string, array{Throwable}> */
    public static function clientErrorProvider(): array
    {
        return [
            'not found' => [new ResourceNotFoundException('/__not_found__')],
            'method not allowed' => [new MethodNotAllowedException('post', 405)],
        ];
    }

    public function testHandsTheWriterTheRenderingUnderTheLogRef(): void
    {
        $e = new LogicException('msg', 500);

        ($this->errorLogger)($e, self::request());

        $this->assertSame([(string) new LogRef($e)], array_keys($this->writer->written));
    }

    private static function request(): RouterMatch
    {
        return new RouterMatch('get', '/');
    }
}
