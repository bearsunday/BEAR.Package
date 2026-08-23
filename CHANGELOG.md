# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Compile steps a module contributes through `MultiBinder` for `CompileStepInterface` now run, each into `{appDir}/var/build/{context}/{binding key}`

### Changed
- Compiled DI scripts move to `{appDir}/var/build/{context}/di`; the old `var/tmp/{context}/di` reads as absent, so recompile after upgrading and point any deploy step that copies it at the new path (#426)
- `Compiler::phar()` writes `{appDir}/app.phar`, beside `autoload.php` and `preload.php`, and no longer takes an output path: collect the archive from the root instead of naming where it lands (#426)
- An archive carries named top-level directories only - `src`, `public`, `bin`, `vendor`, `var`, and wherever an imported application sits - and the build prints `Not packed:` for the rest (#426)
- Until now every other top-level directory shipped: a machine that had run `composer bin tools install` or taken coverage packed `vendor-bin` and `build` into the archive (#426)
- An archive carries one build per application in the tree, the one it was packed for: other contexts stay out, as `var/log` and `var/tmp` do (#426)
- Requires bear/app-meta ^1.13: an invalid application directory is refused with `WriteDirNotAbsoluteException` (a `LogicException`) before any compile work, instead of a late `RuntimeException` (#482)
- An imported application writes under the host's tmp and log (`{hostTmp}/{Vendor}/{Project}/{context}/tmp`), not beside the host under the write base (#426)
- `PharManifest::roots()`, `PharBuilder::__invoke()` and `CompileSteps::run()` take the build directory a compile wrote, not the application directory and context to work one out from; `PackageInjector` and the phar worker no longer take a context at all (#501)
- A boot with a current compile marker returns the compiled scripts without assembling a module tree first
- `Compiler::compile()` writes the compile marker only for a context that boots from the scripts
- A compile empties the whole build directory, so a compile step dropped from the module tree stops shipping the artifacts of the run that still had it
- A boot no longer resolves `AppInterface` to check a build under a marker: resolving through the injector is what reports a broken one
- `CacheDirProvider` and the compile create the directories they write into, instead of relying on `Meta`'s constructor having made them
- `ErrorLogger` sends the rendered exception to `LoggerInterface`, and the logref file is written by a bound `LogRefWriterInterface`: `ProdModule` binds `NullLogRefWriter`, so a production error writes nothing under `logDir` and the trace reaches `error_log` with the summary

### Removed
- `Compiler::fromInjector()` - the injector carried only the application name and directory, and booting one to read them compiled the application an extra time; a build script compiles in its own process (#482)
- `WriteDirMismatchException`, `DelegatedCompileException` and `bin/compile-worker.php`, with the delegated compile they served (#482)
- `PharWriteDirMismatchException`: pack no longer checks that an imported application shares the host's write directory - compile both with the same one; a mismatch fails the boot's marker check instead (#426)
- `CompileRecord::$writeDir` and `PharReport::$writeDir`: the marker no longer carries the write base (#426)
- `CompiledScripts` - `AbstractAppMeta::$buildDir` says where a build is, a caller holding no Meta is handed it, and the DI scripts sit at `/di` under it (#501)
- The injector cache under `{tmpDir}/injector`, with `Injector::getInstance()`'s and `Injector::fromMeta()`'s `$cache`: the compiled scripts are the cache, and a boot no longer needs a writable directory to reuse them

### Fixed
- The directory holding `$entry` ships, so `Compiler::phar('bootstrap/admin.php')` packs an entry outside `public/` instead of refusing it (#426)
- A compile refuses a context that assembles its container per request before `clean()` runs, and `clean()` no longer removes `preload.php`, `autoload.php` or `app.phar`: a compile that fails leaves the last one's files where they were (#426)
- A directory holding an imported application carries that application only: what sits beside it no longer ships (#426)
- The demo tests run in CI, and the demo asserts the `int` its resource declares
- The object graph diagram renders to SVG again: a path fragment pasted into the `which dot` probe had disabled it since 1.10.7

## [1.23.1] - 2026-08-17

### Fixed
- An archive carries the application's own files under `var/` - `var/sql`, `var/conf`, `var/json_schema`, `var/templates`; only `var/log`, `var/tmp` and `var/build` stay out (#426)

## [1.23.0] - 2026-08-17

An application can be packed into one phar and booted read-only. `Compiler::phar()` packs what a compile
just produced, and the archive writes only under the directory named at the build and at the boot.

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

### Removed
- `Injector\AppDirs` (1.22): `Injector\CompiledScripts::dir()` answers where compiled scripts live, `Meta::create()` places an application, and `$meta->writeDir` carries the base it was placed under (#426)
- `InvalidWriteDirException` (1.22): a relative write directory is refused by `BEAR\AppMeta\Exception\WriteDirNotAbsoluteException` (#426)

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
