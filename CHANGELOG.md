# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `Compiler::phar()` — pack the compiled tree into `var/build/{context}.phar`, on both the `new Compiler(...)` and `Compiler::fromInjector(...)` shapes (#426)
- The compile marker is a readable record (`.bear-compile.json`: app, context, tmpDir, writeDir), and the pack reads the write directory from it instead of taking it again (#426)
- The pack reads imports from the compiled `ImportAppConfig` and stops the build when any application was never compiled, writes into the archive, or writes outside the write directory the host was compiled for (#426)
- A boot that cannot rewrite its scripts - an archive, an immutable image - throws `CompiledForAnotherWriteDirException` naming both write directories, instead of failing on the write (#426)
- No file directly under the application root ships in an archive - `composer.json`, `autoload.php`, `preload.php`, `env.json`, `.env` and the rest are the project's, not the artifact's - and no `.env*` file ships wherever it sits (#426)
- The pack refuses an entry the archive does not carry, and refuses an output it could not remove first (#426)
- `Injector::getOverrideInstance()` takes a `$writeDir`, like `getInstance()`: an override injector in a read-only tree needs one too (#426)

### Changed
- `ImportApp::appDir()` replaces the `$appDir` property: the compiled container holds the object, and the build-time path is not the runtime one (#426)
- The compile marker filename is `.bear-compile.json`; a 1.22 marker reads as absent, so recompile after upgrading (#426)
- Requires `ray/compiler ^1.16`, whose `CompiledInjector` accepts a `phar://` script directory (#426)
- Requires `bear/app-meta ^1.12`, which owns the write-directory layout: `Meta::create()` places an application and the Meta carries the base it was placed under (#426)
- `AppDirs` is gone: `Injector\CompiledScripts::dir()` answers where compiled scripts live, and nothing else composes paths (#426)

### Removed
- `InvalidWriteDirException`: a relative write directory is refused by `BEAR\AppMeta\Exception\WriteDirNotAbsoluteException` (#426)

### Fixed
- A stream-URI `appDir` such as `phar://app.phar` without a write directory throws `WriteDirRequiredException`, instead of reporting the tmp directory it cannot create inside the artifact (#426)

## [1.22.1] - 2026-08-14

### Fixed
- A request line with no path (`//`, `///`) answers 400, not the 500 an `assert()` on `parse_url()` gave (#493)
- Call `router->match()` inside the `try` that reaches `throwableHandler`; outside it, a router's client error is an uncaught fatal (#493)
- `RouterCollection` propagates a router's client error instead of answering route-not-found (#493)

## [1.22.0] - 2026-08-13

### Added
- `Injector::fromMeta()` — an injector for an already resolved `Meta` (#482)
- An optional `$writeDir` on `Compiler` and `Injector::getInstance()`, for a tree that is read-only at runtime (#482)

### Changed
- `preload.php` is recorded by a boot of the finished artifact, not by the compile: it holds the boot path and no compiler (#489)
- `preload.php` compiles the DI scripts and AOP proxies that boot loaded with `opcache_compile_file()`; requiring them would run them (#489)
- `preload.php` returns early under `cli`, `phpdbg` and `embed`: a process that serves one request compiles the list and throws it away (#489)
- `preload.php` emits `require` in dependency order again, not `require_once` (#482)
- `FakeRun` transfers the response it fetched, which is what loads the renderer of the response format (#489)
- A build script names the application rather than booting it: `new Compiler($appName, $context, $appDir, $writeDir)` (#489)
- Compiled DI scripts stay under `appDir` and never follow `$writeDir`: the deployment artifact carries them (#482)
- `{writeDir}/{Vendor}/{Project}/{context}/{tmp,log}` keeps applications and contexts apart (#482)
- The compile marker records the writable directory the scripts were compiled for; another one recompiles (#483)
- An on-demand compile is reported through the application logger, not `trigger_error()` (#483)
- Reusing ahead-of-time output requires `ray/aop ^2.20` for weaved-file re-emission (#483)
- `Compiler::compile()` spawns a worker, so it needs `passthru()` and a CLI interpreter (#489)

### Deprecated
- `bin/bear.compile` — use the application's `bin/compile.php` (#482)

### Removed
- The `$prepend` argument of `Compiler::__construct()` / `Compiler::fromInjector()`, a 2021 PHPUnit workaround (#489)

### Fixed
- A compile stops rather than ship a stale `preload.php`: `PreloadRecordException` when the recording boot fails, or when the scripts are not current (#489)
- `FilePutContents` throws `PartialWriteException` on a short write, which would leave a truncated `preload.php` (#489)
- Generated paths go through `var_export()`, so a quote in a directory name cannot break the file (#489)
- `CompileMarker::write()` throws instead of discarding a failed write, and writes through a temporary file (#483)
- `compile()` / `dumpAutoload()` / `clean()` on a `fromInjector()` compiler throw `DelegatedCompileException` (#482)
- `isProd()` no longer reports an unbound `Compile` as dev (#488)
- `clean()` removes `preload.php` and `autoload.php`, and the compile deletes `preload.php` before recording it (#489)

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
