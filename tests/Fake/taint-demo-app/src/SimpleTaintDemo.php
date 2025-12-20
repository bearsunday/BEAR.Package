<?php

declare(strict_types=1);

namespace FakeVendor\TaintDemo;

/**
 * Simple Taint Demo - Uses Psalm's built-in taint sinks.
 */
class SimpleTaintDemo
{
    /**
     * @psalm-taint-source input $userInput
     */
    public function xssVulnerable(string $userInput): void
    {
        // echo is a built-in html sink in Psalm
        echo '<div>' . $userInput . '</div>';
    }

    /**
     * @psalm-taint-source input $cmd
     */
    public function shellVulnerable(string $cmd): void
    {
        // shell_exec is a built-in shell sink
        shell_exec($cmd);
    }

    /**
     * @psalm-taint-source input $file
     */
    public function fileVulnerable(string $file): void
    {
        // file_get_contents with user input - path traversal
        file_get_contents($file);
    }
}
