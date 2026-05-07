<?php

declare(strict_types=1);

namespace BEAR\Package\TaintDemo;

use BEAR\Package\Provide\Router\HttpMethodParams;
use PDO;

/**
 * Taint analysis demonstration
 *
 * This file demonstrates how Psalm's taint analysis detects
 * security vulnerabilities in a BEAR.Sunday application.
 */
final class TaintDemo
{
    /**
     * VULNERABLE: Shell injection via user input
     *
     * HttpMethodParams::get() is marked as @psalm-taint-source input
     * shell_exec() is a shell taint sink
     * Psalm will detect: TaintedShell
     */
    public function shellInjectionDemo(HttpMethodParams $params): string
    {
        [$method, $query] = $params->get($_SERVER, $_GET, $_POST);
        $search = $query['search'] ?? '';

        // VULNERABLE: User input directly in shell command
        return (string) shell_exec('grep -r ' . $search . ' /var/log/');
    }

    /**
     * VULNERABLE: SQL injection via user input
     *
     * Psalm will detect: TaintedSql
     */
    public function sqlInjectionDemo(HttpMethodParams $params, PDO $pdo): array
    {
        [$method, $query] = $params->get($_SERVER, $_GET, $_POST);
        $userId = $query['id'] ?? '';

        // VULNERABLE: User input directly in SQL query
        $stmt = $pdo->query("SELECT * FROM users WHERE id = '" . $userId . "'");

        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * VULNERABLE: XSS via unescaped output
     *
     * Psalm will detect: TaintedHtml
     */
    public function xssDemo(HttpMethodParams $params): void
    {
        [$method, $query] = $params->get($_SERVER, $_GET, $_POST);
        $name = $query['name'] ?? '';

        // VULNERABLE: User input directly in HTML output
        echo '<div>Hello, ' . $name . '</div>';
    }

    /**
     * SAFE: Parameterized query prevents SQL injection
     */
    public function safeSqlDemo(HttpMethodParams $params, PDO $pdo): array
    {
        [$method, $query] = $params->get($_SERVER, $_GET, $_POST);
        $userId = $query['id'] ?? '';

        // SAFE: Using prepared statement with parameter binding
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * SAFE: Escaped output prevents XSS
     */
    public function safeHtmlDemo(HttpMethodParams $params): void
    {
        [$method, $query] = $params->get($_SERVER, $_GET, $_POST);
        $name = $query['name'] ?? '';

        // SAFE: Using htmlspecialchars to escape output
        echo '<div>Hello, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
