# BEAR.Package

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/bearsunday/BEAR.Package/?branch=1.x)
[![codecov](https://codecov.io/gh/bearsunday/BEAR.Package/branch/1.x/graph/badge.svg?token=eh3c9AF4Mr)](https://codecov.io/gh/koriym/BEAR.Package)
[![Type Coverage](https://shepherd.dev/github/bearsunday/BEAR.Package/coverage.svg)](https://shepherd.dev/github/bearsunday/BEAR.Package)
![Continuous Integration](https://github.com/bearsunday/BEAR.Package/workflows/Continuous%20Integration/badge.svg)

## Overview

[BEAR.Sunday](https://github.com/bearsunday/BEAR.Sunday) is a resource-oriented framework that defines the interfaces and abstractions for the application lifecycle — resource handling, dependency injection, and AOP interception. It does not include concrete implementations for routing, caching, error handling, or compilation.

BEAR.Package is the implementation package for BEAR.Sunday. It provides the production-ready bindings that turn those interfaces into a working application: HTTP routing, DI container compilation, query repository caching, `application/vnd.error+json` error pages, PSR-3 logging, and context-based module loading.

## What This Package Provides

| Concern | Implementation |
|---|---|
| Routing | `WebRouter` (HTTP), `CliRouter` (CLI) |
| Error Handling | `DevVndErrorPage` / `ProdVndErrorPage` (`application/vnd.error+json`) |
| Caching | QueryRepository integration, PSR-6 cache |
| Compilation | DI container compiled to PHP scripts, preload file, autoload optimization |
| Logging | `PsrLoggerModule` (Monolog, PSR-3) |
| Context Modules | `ProdModule`, `ApiModule`, `CliModule`, `HalModule` |

## Architecture

### Context-Based DI Binding Override

Instead of switching behavior with a global `MODE` constant and conditionals, BEAR.Package uses a multi-dimensional **context** string to compose DI modules. Each segment of the context corresponds to a module, and later modules override the bindings of earlier ones.

For example, the context `"prod-api-app"` is split by `-`, reversed, and each segment resolves to a Module class:

1. `app` — `AppModule` (loaded first; base bindings + `PackageModule`)
2. `api` — `ApiModule` (overrides default scheme binding to `app://self`)
3. `prod` — `ProdModule` (overrides cache, error handler, and compilation bindings)

The framework's behavior changes entirely through binding overrides — no `if` branches on environment values. Each segment is resolved in order:

1. App namespace first: `{AppName}\Module\{Context}Module`
2. Framework fallback: `BEAR\Package\Context\{Context}Module`

This allows applications to override any context module while falling back to the framework defaults.

## Documentation

Documentation is available at https://bearsunday.github.io/.
