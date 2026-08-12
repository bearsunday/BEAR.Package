# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Deprecated
- `bin/bear.compile` — prefer application `bin/compile.php` with `Compiler::fromInjector(... )()` (still works; see #482)

### Fixed
- Reuse ahead-of-time DI scripts in `PackageInjector::factory()` when a compile marker is present (skip unconditional rebuild; #483)

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
