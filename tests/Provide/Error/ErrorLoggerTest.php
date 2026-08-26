<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Package\FakeLogger;
use BEAR\Package\FakeLogRefWriter;
use BEAR\Sunday\Extension\Router\RouterMatch;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        $logRef = ($this->errorLogger)($e, self::request());

        $detail = $this->writer->written[$logRef];
        $this->assertStringContainsString($e->getTraceAsString(), $detail);
        $this->assertContains($detail, $this->logger->messages['error']);
        $this->assertSame([], $this->logger->messages['debug'] ?? []);
    }

    public function testSendsTheSummaryLineToTheLogger(): void
    {
        $e = new LogicException('msg', 500);

        $logRef = ($this->errorLogger)($e, self::request());

        $this->assertSame((string) new LogRef($e), $logRef);
        $this->assertContains(
            'req:"get /" code:500 e:LogicException(msg) logref:' . $logRef,
            $this->logger->messages['error'],
        );
    }

    /** Below 500 both lines drop together: a logger filtered to errors keeps neither. */
    public function testLogsBothLinesAtDebugWhenNotAnError(): void
    {
        $e = new RuntimeException('msg', 0);

        $logRef = ($this->errorLogger)($e, self::request());

        $this->assertSame([], $this->logger->messages['error'] ?? []);
        $this->assertCount(2, $this->logger->messages['debug']);
        $this->assertContains($this->writer->written[$logRef], $this->logger->messages['debug']);
    }

    public function testHandsTheWriterTheRenderingUnderTheLogRef(): void
    {
        $e = new LogicException('msg', 500);

        $logRef = ($this->errorLogger)($e, self::request());

        $this->assertSame([$logRef], array_keys($this->writer->written));
    }

    private static function request(): RouterMatch
    {
        return new RouterMatch('get', '/');
    }
}
