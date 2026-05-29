<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;

/**
 * Type definitions for BEAR.Package
 *
 * @phpcs:disable SlevomatCodingStandard.Commenting.DocCommentSpacing
 *
 * Domain Types
 * @psalm-type AppName = non-empty-string
 * @psalm-type Context = non-empty-string
 * @psalm-type AppDir = non-empty-string
 * @psalm-type ScriptDir = non-empty-string
 * @psalm-type VarDir = non-empty-string
 * @psalm-type LogDir = non-empty-string
 * @psalm-type TmpDir = non-empty-string
 *
 * Server Arrays
 * @psalm-type ServerArray = array<string, mixed>
 * @psalm-type GlobalsArray = array<string, mixed>
 * @psalm-type CliArgv = list<string>
 * @psalm-type HttpServer = array{REQUEST_METHOD: string, HTTP_X_HTTP_METHOD_OVERRIDE?: mixed, CONTENT_TYPE?: mixed, HTTP_CONTENT_TYPE?: mixed, HTTP_RAW_POST_DATA?: mixed, ...}
 * @phpstan-type HttpServer array{REQUEST_METHOD: string, HTTP_X_HTTP_METHOD_OVERRIDE?: mixed, CONTENT_TYPE?: mixed, HTTP_CONTENT_TYPE?: mixed, HTTP_RAW_POST_DATA?: mixed, ...<string, mixed>}
 *
 * Router Types
 * @psalm-type HttpMethod = non-empty-string
 * @psalm-type ResourceUri = non-empty-string
 * @psalm-type QueryParams = array<string, mixed>
 *
 * Compiler Types
 * @psalm-type ClassList = ArrayObject<int, string>
 * @psalm-type OverwrittenFiles = ArrayObject<int, string>
 * @psalm-type ClassPaths = list<string>
 * @psalm-type LoadedClasses = list<class-string>
 * @psalm-type CompileReport = array{time: string, memory: string, compiled: int, preload: string, dot: string}
 *
 * @phpcs:enable
 */
final class Types
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }
}
