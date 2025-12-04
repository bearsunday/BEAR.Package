# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BEAR.Package is the core framework implementation package for BEAR.Sunday, a resource-oriented PHP framework. It provides dependency injection, AOP compilation, routing, error handling, and context-based module loading.

## Development Commands

### Testing
```bash
# Run all tests
composer test
# Or directly
./vendor/bin/phpunit

# Run specific test suites
./vendor/bin/phpunit --testsuite=core
./vendor/bin/phpunit --testsuite=context
./vendor/bin/phpunit --testsuite=provide

# Run single test file
./vendor/bin/phpunit tests/SomeTest.php

# Run single test method
./vendor/bin/phpunit --filter testMethodName tests/SomeTest.php

# Coverage (with pcov)
composer pcov

# Coverage (with xdebug)
composer coverage
```

### Code Quality
```bash
# Run all quality checks
composer tests

# Code style check
composer cs

# Fix code style
composer cs-fix

# Static analysis
composer sa

# Clean caches
composer clean
```

### Compilation
```bash
# Compile a BEAR.Sunday application
./bin/bear.compile 'VendorName\AppName' context-name /path/to/app

# Example (used in tests)
composer compile
```

## Architecture

### Type Definitions

Domain types are defined in `src/Types.php` using Psalm type aliases:
- `AppName`: Application namespace (e.g., `"MyVendor\\MyApp"`)
- `Context`: Context string (e.g., `"prod-api-app"`)
- `AppDir`: Application directory path

These types are imported via `@psalm-import-type` throughout the codebase.

### Core Components

**Injector System**
- `Injector::getInstance()`: Creates/retrieves cached DI container for an application
- `PackageInjector`: Internal injector implementation with caching support
- Uses Ray.Di for dependency injection with compile-time optimization

**Compiler**
- Compilation process that generates optimized autoload, preload, DI scripts, and object graphs
- `Compiler::compile()`: Main compilation orchestrator
- Sub-compilers: `CompileAutoload`, `CompilePreload`, `CompileObjectGraph`, `CompileClassMetaInfo`
- Tracks loaded classes during compilation for preload generation
- Uses Ray.Compiler to compile DI container to PHP scripts

**Module System**
- `Module` class: Context-based module loader that processes hyphenated contexts (e.g., "prod-api-app")
- Context modules are loaded in reverse order (right-to-left)
- Looks for modules in app namespace first, falls back to `BEAR\Package\Context\`
- Built-in contexts: `ProdModule`, `ApiModule`, `CliModule`, `HalModule`
- `AbstractAppModule`: Base class for app modules that need `AbstractAppMeta` injection

**PackageModule**
- Base framework module that installs core functionality
- Installs: QueryRepository, WebRouter, VndError, PsrLogger, Stream, CreatedResource, DiCompile, Sunday modules
- Override pattern with `Psr6NullModule` for cache configuration

### Directory Structure

```
src/
├── Annotation/          # Framework annotations (@ReturnCreatedResource, @StdIn)
├── Compiler/            # Compilation components
├── Context/             # Built-in context modules (Prod, Api, Cli, Hal)
├── Exception/           # Framework exceptions
├── Injector/            # DI container implementation
├── Module/              # Core modules and providers
├── Provide/
│   ├── Error/          # Error handling (DevVndErrorPage, ProdVndErrorPage, ErrorHandler)
│   ├── Logger/         # PSR-3 logger integration (Monolog)
│   ├── Representation/ # Resource representation (CreatedResource)
│   ├── Router/         # Routers (WebRouter, CliRouter, RouterCollection)
│   └── Transfer/       # Response transfer (CliResponder)
├── AbstractAppModule.php
├── Compiler.php
├── Injector.php
├── Module.php
└── PackageModule.php

src-deprecated/          # Deprecated code for BC
tests/Fake/             # Test fixtures and fake applications
```

### Key Patterns

**Context-Based Configuration**
- Applications can have multiple contexts (e.g., "prod-api-app", "dev-cli-app")
- Each context segment maps to a Module (AppModule, ApiModule, ProdModule)
- Modules are installed right-to-left, allowing progressive overrides

**Dependency Injection**
- Constructor injection with type hints
- Interface binding in modules
- Provider classes for complex object creation
- AOP interceptors via `bindInterceptor()`

**Resource-Oriented**
- Resources are the primary abstraction (not MVC)
- Two resource types:
  - `Page` resources: Web page endpoints (like controllers)
  - `App` resources: Internal application resources (like API endpoints)
- Resource classes extend `ResourceObject` and use HTTP method handlers:
  - `onGet()`, `onPost()`, `onPut()`, `onPatch()`, `onDelete()`
- Resources set `$this->body` and return `$this`
- `@Link` or `#[Link]` attributes define hypermedia relations
- Router maps HTTP/CLI requests to resource URIs
- Error responses use vnd.error media type

**Routing**
- `WebRouter`: Converts HTTP requests to resource URIs (scheme + host + path)
- `CliRouter`: Converts CLI arguments to resource URIs
  - CLI format: `php public/index.php {method} {uri} [query params]`
  - Example: `php public/index.php get /user?id=1`
  - Wraps another router and converts CLI context to web context
- Both routers implement `RouterInterface`

**Compilation for Performance**
- DI container compiled to PHP code
- Preload file generation for opcache
- Autoload optimization
- Object graph visualization (DOT format)

## Test Applications

The `tests/Fake/` directory contains example applications:
- `fake-app`: Full-featured test application with resources, modules, providers
- `fake-min-app`: Minimal application structure
- `import-app`: Tests module import functionality

These serve as both test fixtures and reference implementations.

## PHP Requirements

- PHP 8.2+
- Extensions: hash
- Ray.Di for dependency injection
- Ray.Aop for aspect-oriented programming
- PHP 8 attributes support (annotations deprecated)

## CI/CD

- GitHub Actions workflow: `.github/workflows/continuous-integration.yml`
- Uses shared Ray.Di workflow from `ray-di/.github`
- Tests against PHP 8.1, 8.2, 8.3, 8.4
- Runs on push, pull requests, and manual workflow dispatch

## Important Notes

- Active development branch: `1.x`
- `src-deprecated/` contains backward compatibility code
- Tests use PSR-4 autoloading for fake applications
- Compiler generates files in `var/` directory (gitignored)
