# Psalm Taint Annotation Patches

These patches add Psalm taint annotations to BEAR.Sunday ecosystem packages for security analysis.

## Applying Patches and Creating PRs

**Note:** After applying each patch, run `composer cs-fix` to ensure code style compliance.

### BEAR.Resource

```bash
git clone https://github.com/bearsunday/BEAR.Resource.git
cd BEAR.Resource
git checkout -b add-psalm-taint-annotations
git am < bear-resource-taint.patch
composer cs-fix  # Fix code style if needed
git add -A && git commit --amend --no-edit  # Include cs fixes
git push -u origin add-psalm-taint-annotations
gh pr create --title "Add Psalm taint annotations for security analysis" --body "$(cat <<'EOF'
## Summary

Add Psalm taint annotations to enable static security analysis for detecting:
- XSS vulnerabilities
- SSRF attacks
- SQL injection (when used with database modules)

## Changes

- `@psalm-taint-source input` on:
  - `AssistedWebContextParam::__invoke()` - Web context parameters
  - `InputParam::__invoke()` - Query parameters
  - `InputFormParam::__invoke()` - File uploads
  - `InputFormsParam::__invoke()` - Multiple file uploads
  - `Uri::__construct()` - URI query parameters
  - `AbstractRequest::__invoke()` - Request query parameters
  - `HttpRequestCurl::parseBody()` - External HTTP response body

- `@psalm-taint-sink ssrf` on:
  - `HttpRequestCurl::request()` - Prevents SSRF attacks
  - `HttpRequestCurl::initializeCurl()` - Internal curl initialization

- `@psalm-taint-escape html` on:
  - `JsonRenderer::render()` - JSON encoding escapes HTML
  - `HalRenderer::render()` - HAL+JSON encoding escapes HTML

## Test Plan

- [ ] Run `./vendor/bin/psalm --taint-analysis` to verify annotations work
- [ ] Existing tests pass
EOF
)"
```

### Ray.AuraSqlModule

```bash
git clone https://github.com/ray-di/Ray.AuraSqlModule.git
cd Ray.AuraSqlModule
git checkout -b add-psalm-taint-annotations
git am < ray-aura-sql-module-taint.patch
git push -u origin add-psalm-taint-annotations
gh pr create --title "Add Psalm taint annotations for SQL injection analysis" --body "$(cat <<'EOF'
## Summary

Add Psalm taint annotations to enable static security analysis for SQL injection detection.

## Changes

- `@psalm-taint-sink sql` on:
  - `ExtendedPdoAdapter::__construct()` - SQL query parameter
  - `AuraSqlPagerFactory::newInstance()` - SQL query for pagination

- `@psalm-taint-sink sql` + `@psalm-taint-escape sql` on:
  - `FetchAssoc::__invoke()` - Parameterized queries escape SQL
  - `FetchEntity::__invoke()` - Parameterized queries escape SQL

The escape annotation indicates that when using prepared statements with bound parameters, the SQL injection risk is mitigated.

## Test Plan

- [ ] Run `./vendor/bin/psalm --taint-analysis` to verify annotations work
- [ ] Existing tests pass
EOF
)"
```

### Madapaja.TwigModule

```bash
git clone https://github.com/madapaja/Madapaja.TwigModule.git
cd Madapaja.TwigModule
git checkout -b add-psalm-taint-annotations
git am < madapaja-twig-module-taint.patch
git push -u origin add-psalm-taint-annotations
gh pr create --title "Add Psalm taint annotations for XSS prevention" --body "$(cat <<'EOF'
## Summary

Add Psalm taint annotations to mark Twig rendering as HTML-safe (due to Twig's autoescape feature).

## Changes

- `@psalm-taint-escape html` on:
  - `TwigRenderer::render()` - Twig autoescapes by default
  - `ErrorPagerRenderer::render()` - Error page rendering

This allows Psalm's taint analysis to understand that data passing through Twig templates is properly escaped for HTML output.

## Test Plan

- [ ] Run `./vendor/bin/psalm --taint-analysis` to verify annotations work
- [ ] Existing tests pass
EOF
)"
```

### Ray.AuraSessionModule

```bash
git clone https://github.com/ray-di/Ray.AuraSessionModule.git
cd Ray.AuraSessionModule
git checkout -b add-psalm-taint-annotations
git am < ray-aura-session-module-taint.patch
git push -u origin add-psalm-taint-annotations
gh pr create --title "Add Psalm taint annotations for session/cookie security" --body "$(cat <<'EOF'
## Summary

Add Psalm taint annotations to mark session and cookie providers as taint sources since `$_COOKIE` contains user-controlled data.

## Changes

- `@psalm-taint-source input` on:
  - `SessionProvider::get()` - Returns Session initialized with $_COOKIE
  - `CookieProvider::get()` - Returns $_COOKIE directly

These annotations enable Psalm's taint analysis to track potentially dangerous user input through session and cookie handling.

## Test Plan

- [ ] Run `./vendor/bin/psalm --taint-analysis` to verify annotations work
- [ ] Existing tests pass
EOF
)"
```

### Qiq

```bash
git clone https://github.com/qiqphp/qiq.git
cd qiq
git checkout -b add-psalm-taint-annotations
git am < qiq-taint.patch
git push -u origin add-psalm-taint-annotations
gh pr create --title "Add Psalm taint annotations for security analysis" --body "$(cat <<'EOF'
## Summary

Add Psalm taint annotations to the Escape helper methods for static security analysis.

## Changes

- `@psalm-taint-escape html` on:
  - `Escape::h()` - HTML escape via `htmlspecialchars()`
  - `Escape::a()` - HTML attribute escape
  - `Escape::j()` - JavaScript escape (safe for HTML context)
  - `Escape::u()` - URL escape

- `@psalm-taint-escape css` on:
  - `Escape::c()` - CSS escape

These annotations enable Psalm's taint analysis to track that data passing through these escape methods is properly sanitized.

## Test Plan

- [ ] Run `./vendor/bin/psalm --taint-analysis` to verify annotations work
- [ ] Existing tests pass
EOF
)"
```

## Patch Contents Summary

| Package | Annotations |
|---------|-------------|
| bear/resource | `@psalm-taint-source input`, `@psalm-taint-sink ssrf`, `@psalm-taint-escape html` |
| ray/aura-sql-module | `@psalm-taint-sink sql`, `@psalm-taint-escape sql` |
| ray/aura-session-module | `@psalm-taint-source input` |
| madapaja/twig-module | `@psalm-taint-escape html` |
| qiq/qiq | `@psalm-taint-escape html`, `@psalm-taint-escape css` |

## CI Integration

Add the taint analysis job to `.github/workflows/continuous-integration.yml`:

```yaml
  taint-analysis:
    name: Psalm Taint Analysis
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          coverage: none

      - name: Install dependencies
        run: composer install --no-progress --prefer-dist

      - name: Run Psalm Taint Analysis on main codebase
        run: ./vendor/bin/psalm --taint-analysis

      - name: Verify demo detects taint issues
        run: |
          OUTPUT=$(./vendor/bin/psalm --taint-analysis tests/Fake/taint-demo-app/src/ 2>&1) || true
          if echo "$OUTPUT" | grep -q "TaintedHtml"; then
            echo "OK: Taint analysis correctly detected XSS vulnerability"
          else
            echo "ERROR: Demo should trigger TaintedHtml detection"
            exit 1
          fi
```

See `ci-taint-analysis.yml` for the complete job definition.

## References

- [Psalm Taint Analysis Documentation](https://psalm.dev/docs/security_analysis/)
- [BEAR.Package Taint Demo](../tests/Fake/taint-demo-app/)
