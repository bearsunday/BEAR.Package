# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.22.0] - 2026-08-13

### Added
- `Injector::fromMeta()` — injector for an already resolved `Meta` (#482)

### Changed
- `preload.php` records what a boot loads, not what the compile loads. The compile process was never able to answer the question: it holds the compiler and the module tree, and it had already loaded the boot path before its tracker started. The compile now spawns a worker that boots the application from the finished artifact — scripts, marker and meta caches in place — and writes `preload.php` from the classes that boot pulls in. Measured on a five-resource application: 50 classes the boot loads were missing and 40 the boot never loads were present; both counts are now 2 and 7 (#489)
- `preload.php` compiles the DI scripts and AOP proxies the boot loaded with `opcache_compile_file()`. They cannot be required — a DI script builds an instance from variables that only exist in the injector's scope — and preload links what it compiled after the file has run, so no ordering applies. The list is measured, never a glob of the script directory: a proxy whose parent the boot never loaded cannot be linked, and PHP says so at every startup (#489)
- `FakeRun` transfers the response through the real responder, buffered, so the responder and `Output` are recorded; `Compiler\Bootstrap` calls `isNotModified()` like a real entry point, which is what loads `BEAR\QueryRepository\Header` (#489)
- `preload.php` emits `require` in dependency order again, not `require_once` (#482)
- `Compiler::__construct()` and `Compiler::fromInjector()` take an optional `$writeDir`, and `Injector::getInstance()` takes one too: an application writing outside its own tree hands one directory, and `{writeDir}/{Vendor}/{Project}/{context}/{tmp,log}` keeps applications and contexts apart (#482)
- Compiled DI scripts stay under `appDir` and never follow `$writeDir`: they ship in the deployment artifact, so a cold start on a read-only platform reads them instead of compiling again (#482)
- The compile marker records the writable directory the scripts were compiled for; booting with another one recompiles rather than answering with the old paths (#483)
- `compile()` / `dumpAutoload()` / `clean()` on a `fromInjector()` compiler throw `DelegatedCompileException` (#482)
- An on-demand compile is reported through the application logger, not `trigger_error()` (#483)
- Reusing ahead-of-time output requires `ray/aop ^2.20` for weaved-file re-emission (#483)
- `isProd()` no longer reports an unbound `Compile` as dev (#488)
- `clean()` removes `preload.php` and `autoload.php` too, and the compile deletes `preload.php` before recording it: the check that the worker wrote one can no longer pass on the last deploy's file (#489)
- A compile whose recording boot fails stops with `PreloadRecordException` instead of shipping a stale or absent `preload.php`. It also refuses a context that assembles the container per request, and one whose compiled scripts are not current — recording either would write the assembler into `preload.php` (#489)

### Removed
- The `$prepend` argument of `Compiler::__construct()` / `Compiler::fromInjector()`. It turned the class-tracking autoloader's queue position into a public knob for one 2021 workaround ("Set autoloder prepend off for phpunit"); `PreloadClassFilter` has since made the position irrelevant to what `preload.php` records

### Deprecated
- `bin/bear.compile` — prefer application `bin/compile.php` with `Compiler::fromInjector(... )()` (still works; see #482)

### Fixed
- `Compiler::fromInjector()` compiles in a clean child process, so `preload.php` is complete (#482)
- Reuse ahead-of-time DI scripts when a compile marker is present, and boot from a read-only script directory (#483)
- `CompileMarker::write()` throws instead of discarding a failed write (#483)

## [1.21.0] - 2026-07-10

### Added
- `Compiler::fromInjector(InjectorInterface $injector, string $context)` so apps compile with the same injector/Meta as runtime (#480)
- `Compiler::__invoke()` (clean → compile → dumpAutoload) and `Compiler::clean()` for CLI entry points (#480)

### Changed
- `CompilePreload` reuses the given Meta (no second `new Meta`) (#480)
- FakeRun no longer calls `TransferInterface` (avoids unbuffered `header()` during compile) (#480)

### Notes
- Optional Meta `tmpDir` / `logDir` overrides need `bear/app-meta` `^1.11` (still compatible with `^1.9`)

## [1.20.4] - 2026-07-09

### Fixed
- Isolate override injector scriptDir so AOP proxies / compiled containers do not collide with the default injector for the same app+context (#478, #479)
- Align `Compiler::compile()` DI script directory with runtime (`{tmpDir}/di` instead of `var/di/{context}`) (#479)
- Fix PHPStan 2 `impossibleType` on `class_exists(HttpCacheInterface::class)` in `FakeRun` — use `interface_exists()` (#479)

## [1.20.3] - 2026-04-09

### Fixed
- Fix `ReflectionException` on AOP proxy class names during prod compilation in `PackageInjector::factory()` (#467, #468)

## [1.20.2] - 2026-03-26

### Changed
- `Compiler::compile()` returns `CompileReport` array instead of echo output
- Remove `FileUpdate` from dev injector lifecycle
- Modernize demo app: PHP 8 attributes, type declarations, `Throwable` catch

### Fixed
- Remove `ResourceObjectModule` from `ImportAppModule` to fix resource object binding (#466)
- Fix SA workflow: update PHP version to 8.4

## [1.20.1] - 2026-01-25

### Fixed
- Use `ScriptInjectorInterface` instead of deprecated `ScriptInjector` in cacheability check
  - Avoids unnecessary `FileUpdate` directory traversal when using `CompiledInjector`
  - Benchmark (minimal skeleton app): function calls -67%, memory -5%

## [1.20.0] - 2025-11-22

### Changed
- Bump bear/streamer to ^1.4
- Bump koriym/http-constants to ^1.2
- Remove abandoned Doctrine packages
- Update to stable dependencies and PHP 8.2 minimum
- Replace array shape annotations with domain types from Types.php
- Replace @var annotations with native property types
- Update demo to use modern Injector::getInstance() API
- Upgrade PHPUnit from 9 to 11

### Fixed
- Fix ImportAppModule to bind imported resource objects
- Fix composer test issues

### Deprecated
- Deprecate unused CacheDirProvider binding

### Removed
- Remove dummy 'bin' script to avoid Composer command conflict

## [1.19.0] - 2025-10-27

### Changed
- Migrate built-in compiler implementation to Ray.Compiler module
- Add PHP 8.5 to CI test matrix
- Remove all global Psalm suppressions
- Created `src/Types.php` with domain type definitions
- Comply with newer Psalm requirements (e.g., `ClassMustBeFinal`, type assertions)
- Psalm type inference: 99.7576%
- Update `ray/compiler` to `^1.12`
- Add `symfony/polyfill-php83`

## [1.18.0] - 2025-01-11

### Changed
- Enable PHP 8.4 support
- Add Windows compatibility
- Update license copyright year(s)
