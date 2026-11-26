<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use function array_key_exists;
use function in_array;
use function is_scalar;
use function is_string;

/** The $_SERVER and $_ENV values this process set, for a worker to configure modules as this process does. */
final class WorkerGlobals
{
    /** Keys a PHP process sets for itself; the worker keeps its own. */
    private const array PROCESS_SERVER_KEYS = ['argc', 'PHP_SELF', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PATH_TRANSLATED', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'REQUEST_TIME_FLOAT'];

    /**
     * @param array<array-key, mixed> $server
     * @param array<array-key, mixed> $env
     * @param array<string, string>   $processEnv what the worker inherits as its environment
     *
     * @return array{server: array<string, scalar>, env: array<string, scalar>}
     */
    public function __invoke(array $server, array $env, array $processEnv): array
    {
        return ['server' => $this->setInProcess($server, $processEnv), 'env' => $this->setInProcess($env, $processEnv)];
    }

    /**
     * @param array<array-key, mixed> $globals
     * @param array<string, string>   $processEnv
     *
     * @return array<string, scalar>
     */
    private function setInProcess(array $globals, array $processEnv): array
    {
        $set = [];
        foreach ($globals as $key => $value) {
            if (! is_string($key) || ! is_scalar($value) || in_array($key, self::PROCESS_SERVER_KEYS, true)) {
                continue;
            }

            if (array_key_exists($key, $processEnv) && $processEnv[$key] === (string) $value) {
                continue;
            }

            $set[$key] = $value;
        }

        return $set;
    }
}
