<?php

declare(strict_types=1);

namespace FakeVendor\TaintDemo;

/**
 * Taint Analysis Demo
 *
 * This class demonstrates Psalm's taint analysis capabilities.
 * Run: ./vendor/bin/psalm --taint-analysis tests/Fake/taint-demo-app/src/TaintDemo.php
 */
class TaintDemo
{
    /**
     * VULNERABLE: Direct HTML echo with user input.
     *
     * @param string $userInput User input from request
     *
     * @psalm-taint-source input $userInput
     */
    public function vulnerableHtml(string $userInput): void
    {
        // TaintedHtml: user input directly echoed as HTML
        $this->outputHtml('<div>Hello, ' . $userInput . '!</div>');
    }

    /**
     * VULNERABLE: Direct SQL query with user input.
     *
     * @param string $userId User ID from request
     *
     * @psalm-taint-source input $userId
     */
    public function vulnerableSql(string $userId, \PDO $pdo): void
    {
        // TaintedSql: user input directly in SQL
        $sql = "SELECT * FROM users WHERE id = '" . $userId . "'";
        $this->executeSql($pdo, $sql);
    }

    /**
     * VULNERABLE: Direct shell command with user input.
     *
     * @param string $filename Filename from request
     *
     * @psalm-taint-source input $filename
     */
    public function vulnerableShell(string $filename): void
    {
        // TaintedShell: user input in shell command
        $this->executeCommand('cat ' . $filename);
    }

    /**
     * SAFE: HTML escaped output.
     *
     * @param string $userInput User input from request
     *
     * @psalm-taint-source input $userInput
     */
    public function safeHtml(string $userInput): void
    {
        // Safe: escapeHtml has @psalm-taint-escape html
        $escaped = $this->escapeHtml($userInput);
        $this->outputHtml('<div>Hello, ' . $escaped . '!</div>');
    }

    /**
     * SAFE: Parameterized SQL query.
     *
     * @param string $userId User ID from request
     *
     * @psalm-taint-source input $userId
     */
    public function safeSql(string $userId, \PDO $pdo): void
    {
        // Safe: prepare() with bound parameters
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    /**
     * Output HTML content.
     *
     * @psalm-taint-sink html $html
     */
    private function outputHtml(string $html): void
    {
        echo $html;
    }

    /**
     * Execute SQL query.
     *
     * @psalm-taint-sink sql $sql
     */
    private function executeSql(\PDO $pdo, string $sql): void
    {
        $pdo->query($sql);
    }

    /**
     * Execute shell command.
     *
     * @psalm-taint-sink shell $command
     */
    private function executeCommand(string $command): void
    {
        shell_exec($command);
    }

    /**
     * Escape HTML special characters.
     *
     * @psalm-taint-escape html
     */
    private function escapeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
