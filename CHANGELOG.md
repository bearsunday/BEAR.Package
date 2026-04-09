# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.20.3] - 2026-04-09

### Fixed
- Fix `PackageInjector::factory()` reusing a module instance mutated by
  Ray.Di's AOP weaving. Constructing a `RayInjector` for prod detection
  wove AOP aspects into the module's container, rewriting Dependency
  class names to proxy names. Passing the same module to
  `Ray\Compiler\Compiler::compile()` afterwards could raise
  `ReflectionException` on the proxy class name. Prod is now detected by
  reading `Compile::class` directly from the container, so no
  `RayInjector` is constructed for detection (#467, #468)

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
