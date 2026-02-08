# BEAR.Package Demo

Minimal [BEAR.Sunday](https://bearsunday.github.io/) application demonstrating resource-oriented architecture with [BEAR.Package](https://github.com/bearsunday/BEAR.Package).

## Features

- Resource embedding with `#[Embed]`
- Hypermedia links with `#[Link]`
- Query repository caching with `#[Cacheable]`
- Context-based dependency bindings (`cli-hal-app`, `hal-app`)

## Resource Structure

```text
page://self/index           Index (greeting)
page://self/user            User page
  └── #[Embed] /api/user    API User (#[Cacheable])
        ├── #[Embed] /api/website    Website
        ├── #[Embed] /api/contact    Contact
        │     └── #[Embed] /api/user/friend    Friend list
        └── #[Link] /api/profile     Profile (link)
```

## Install

```bash
composer install
```

## Run

Console:

```bash
php public/index.php get /
php public/index.php get '/user?id=1'
CONTEXT=prod-hal-app php public/index.php get /
```

Web server:

```bash
composer serve
# http://127.0.0.1:8080/
```

## Test

```bash
./vendor/bin/phpunit
```
