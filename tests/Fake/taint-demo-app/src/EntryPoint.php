<?php

declare(strict_types=1);

namespace FakeVendor\TaintDemo;

/**
 * Entry point demonstrating taint flow from $_GET to sinks.
 *
 * This simulates a web request where user input flows through the application.
 */
class EntryPoint
{
    /**
     * Simulate handling a web request.
     *
     * Data flows: $_GET (source) -> method parameter -> sink
     */
    public function handleRequest(): void
    {
        // $_GET is a built-in taint source in Psalm
        $userInput = $_GET['name'] ?? '';
        $userId = $_GET['id'] ?? '';
        $filename = $_GET['file'] ?? '';

        $demo = new SimpleTaintDemo();

        // These should trigger taint errors:
        $demo->xssVulnerable($userInput);  // TaintedHtml
        $demo->shellVulnerable($filename); // TaintedShell
        $demo->fileVulnerable($filename);  // TaintedFile/TaintedInput
    }

    /**
     * Direct taint flow - most basic test.
     */
    public function directXss(): void
    {
        // $_GET is taint source, echo is html sink
        echo $_GET['xss'];
    }

    /**
     * Direct SQL injection.
     */
    public function directSql(\PDO $pdo): void
    {
        // $_GET is source, query() is sql sink
        $pdo->query("SELECT * FROM users WHERE id = '" . $_GET['id'] . "'");
    }

    /**
     * Direct command injection.
     */
    public function directShell(): void
    {
        // $_GET is source, shell_exec is shell sink
        shell_exec($_GET['cmd']);
    }
}
