# Psalm Taint Annotations for BEAR.Sunday Ecosystem

This directory contains patches and workflows for adding Psalm taint analysis support to the BEAR.Sunday ecosystem.

## Overview

Psalm's taint analysis tracks data flow from untrusted sources (user input) to dangerous sinks (SQL queries, shell commands, HTML output), detecting security vulnerabilities like:

- **TaintedSql** - SQL injection
- **TaintedShell** - Command injection
- **TaintedHtml** - Cross-site scripting (XSS)
- **TaintedFile** - Path traversal
- **TaintedSsrf** - Server-side request forgery

## Annotations

### Taint Sources
Mark methods that return untrusted user input:
```php
/**
 * @psalm-taint-source input
 */
public function get(array $server, array $get, array $post);
```

### Taint Sinks
Mark methods where tainted data is dangerous:
```php
/**
 * @psalm-taint-sink sql $query
 */
public function query(string $query);
```

### Taint Escapes
Mark methods that sanitize data:
```php
/**
 * @psalm-taint-escape html
 */
public function h(string $value): string;
```

## Patch Files

| File | Target Package | Description |
|------|---------------|-------------|
| `bear-resource-taint.patch` | bearsunday/BEAR.Resource | Marks request invocation as taint source |
| `ray-aura-sql-module-taint.patch` | ray/aura-sql-module | Marks SQL operations |
| `madapaja-twig-module-taint.patch` | madapaja/twig-module | Marks Twig output as escaped |
| `qiq-taint.patch` | qiq/qiq | Marks Escape class methods |

## BEAR.Package Annotations

The following files in BEAR.Package have taint annotations:

| File | Annotation |
|------|-----------|
| `src/Provide/Router/HttpMethodParams.php` | `@psalm-taint-source input` |
| `src/Provide/Router/HttpMethodParamsInterface.php` | `@psalm-taint-source input` |

## Demo Application

The `tests/Fake/taint-demo-app/` directory contains examples demonstrating taint detection:

```php
// VULNERABLE - Psalm detects TaintedShell
[$method, $query] = $params->get($_SERVER, $_GET, $_POST);
shell_exec('grep ' . $query['search']);

// VULNERABLE - Psalm detects TaintedSql
$pdo->query("SELECT * FROM users WHERE id = '" . $query['id'] . "'");

// VULNERABLE - Psalm detects TaintedHtml
echo '<div>' . $query['name'] . '</div>';

// SAFE - Using prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $query['id']]);

// SAFE - Using htmlspecialchars
echo '<div>' . htmlspecialchars($query['name']) . '</div>';
```

## Running Taint Analysis

```bash
# Run taint analysis on the demo
./vendor/bin/psalm --taint-analysis tests/Fake/taint-demo-app/src/

# Run on your application
./vendor/bin/psalm --taint-analysis src/
```

## CI Integration

### Taint Analysis Workflow

Copy `ci-taint-analysis.yml` to `.github/workflows/`:

```bash
cp docs/patches/ci-taint-analysis.yml .github/workflows/
```

This workflow runs Psalm taint analysis on push and shows detected vulnerabilities.

### Automated PR Creation

To automatically create PRs to ecosystem packages:

1. Copy workflow:
   ```bash
   cp docs/patches/create-taint-prs.yml .github/workflows/
   ```

2. Create a Personal Access Token (PAT):
   - GitHub Settings → Developer settings → Personal access tokens
   - Create token with `repo` scope
   - Token needs write access to target repositories

3. Add secret:
   - Repository Settings → Secrets and variables → Actions
   - Create `ECOSYSTEM_PAT` secret with the PAT value

4. Run workflow:
   - Actions → "Create Taint Annotation PRs"
   - Check "Create PRs to ecosystem packages"
   - Click "Run workflow"

## Package-Specific Notes

### Qiq Templates

Qiq's `Escape` class provides taint-escaping methods:

```php
use Qiq\Escape;

$escape = new Escape();
echo $escape->h($userInput);  // Escaped - no TaintedHtml
```

### Twig Templates

Twig auto-escapes by default. The `TwigRenderer` is marked with `@psalm-taint-escape html`.

### Database Queries

Always use prepared statements with parameter binding:

```php
// SAFE
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);

// UNSAFE - triggers TaintedSql
$pdo->query("SELECT * FROM users WHERE id = $userId");
```
