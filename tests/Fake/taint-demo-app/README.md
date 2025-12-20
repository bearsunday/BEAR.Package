# Psalm Taint Analysis Demo

This demo shows how Psalm's taint analysis detects security vulnerabilities.

## Running Taint Analysis

```bash
# From BEAR.Package root:
./vendor/bin/psalm --taint-analysis tests/Fake/taint-demo-app/src/
```

## Expected Output

Psalm will detect:
- **TaintedHtml** - XSS vulnerabilities (user input in HTML output)
- **TaintedShell** - Command injection (user input in shell commands)
- **TaintedSql** - SQL injection (user input in SQL queries)
- **TaintedFile** - Path traversal (user input in file paths)

## Demo Files

### EntryPoint.php
Shows direct vulnerabilities with `$_GET` as the taint source:
- `directXss()` - `echo $_GET['xss']`
- `directShell()` - `shell_exec($_GET['cmd'])`
- `directSql()` - `$pdo->query("..." . $_GET['id'])`

### SimpleTaintDemo.php
Shows taint flow through method parameters.

### TaintDemo.php
Shows both vulnerable and safe patterns:
- Vulnerable methods that Psalm catches
- Safe methods using escape functions with `@psalm-taint-escape`

## Key Annotations

```php
// Mark parameter as tainted (user input)
@psalm-taint-source input $userInput

// Mark function as dangerous (sink)
@psalm-taint-sink html $output
@psalm-taint-sink sql $query
@psalm-taint-sink shell $command

// Mark function as sanitizer (escape)
@psalm-taint-escape html
@psalm-taint-escape sql
```

## Framework Integration

BEAR.Sunday framework packages have taint annotations:
- **WebRouter/CliRouter**: `@psalm-taint-source input` on request params
- **TwigRenderer/QiqEscape**: `@psalm-taint-escape html`
- **AuraSqlModule**: `@psalm-taint-sink sql` + `@psalm-taint-escape sql` for prepared statements
