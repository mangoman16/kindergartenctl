# Claude Instructions: Kindergarten Spiele Organizer

## Project Overview

A PHP 8.0+ web application for kindergarten teachers to organize educational games, materials, and storage boxes. Custom MVC framework (no Laravel/Symfony). German-language UI.

## Which MD Files to Check for What

| File | Purpose | When to Check |
|------|---------|---------------|
| `CLAUDE.md` | Claude AI instructions, project map, known patterns | **Always** - read first |
| `BUG_AUDIT.md` | All bugs found and fixed, with file:line references | Before fixing bugs, to avoid regressions |
| `SECURITY_AUDIT.md` | Security findings (XSS, CSRF, SQLi, auth) | When modifying auth, input handling, or DB queries |
| `CODE_QUALITY.md` | Code quality patterns, inconsistencies | When refactoring or adding new features |
| `README.md` | Installation, setup, requirements | When changing config, dependencies, or install flow |
| `project.md` | Full specification (features, DB schema, routes) | When adding features or understanding business logic |
| `todo.md` | Development task tracking | When checking what's done vs pending |

## Architecture Quick Reference

```
public/index.php          -> Entry point (bootstraps App)
src/core/App.php          -> Loads config, starts session, dispatches router
src/core/Router.php       -> URL pattern matching, calls controller methods
src/core/Controller.php   -> Base class: auth, CSRF, views, redirects
src/core/Model.php        -> Base class: CRUD, query builder, pagination
src/core/Database.php     -> PDO singleton, schema, migrations
src/core/Auth.php         -> Session-based auth, login/logout, remember-me
src/core/Validator.php    -> Server-side form validation rules
src/config/routes.php     -> All GET/POST route definitions
src/lang/de.php           -> All German translation strings (flat key=>value)
```

## Critical Patterns to Follow

### 1. Redirects Always Exit
`Router::redirect()` and `Router::back()` both call `exit`. So `requireAuth()` and `requireCsrf()` DO halt execution. Do NOT add redundant `return` or `exit` after them.

### 2. PDO Emulated Prepares Are OFF
`ATTR_EMULATE_PREPARES => false` means you CANNOT reuse named parameters in a single query. Use distinct names (`:start1`, `:start2`, etc.) like `CalendarEvent::getForRange()` does.

### 3. Translation Function `__()`
- Loads `src/lang/de.php` once (static cache)
- Keys use dot notation: `'game.title'`, `'auth.login'`
- Returns the key string itself if not found (no exception)
- `auth.logout` = "Abmelden" (nav button label)
- `auth.logged_out` = "Sie wurden abgemeldet." (flash message after logout)

### 4. Date Formatting
- `formatDate()` replaces `F` before `M` to prevent cascading corruption
- `formatTimeAgo()` casts `floor()` to `(int)` for strict comparison
- `parseGermanDate()` validates with `DateTime::getLastErrors()`

### 5. Security Helpers
- `sanitizeFilename()` checks full filename for dotfiles (`.htaccess`, `.env`) and name-without-ext for extensionless entries (`passwd`, `shadow`)
- `encryptValue()` throws RuntimeException on failure (never returns plaintext)
- `decryptValue()` handles both `enc:` prefixed (encrypted) and legacy plaintext values

### 6. SMTP Password Storage
Both `InstallController::saveEmail()` and `SettingsController::updateSmtp()` encrypt the SMTP password with `encryptValue()` before writing to `storage/smtp.php`. The `Mailer` calls `decryptValue()` when reading.

### 7. Changelog Logging
Use `ChangelogService::getInstance()->logCreate/logUpdate/logDelete()` for audit logging. Do NOT write raw SQL to the changelog table (some controllers had this inconsistency).

### 8. Image Handling
Use `ImageProcessor` for uploading and deleting images. Do NOT use manual `unlink()` calls.

## Common Bug Patterns to Watch For

1. **`floor()` returns float** - Always cast to `(int)` before strict `===` comparison
2. **Duplicate array keys in PHP** - Second silently overwrites first (no warning)
3. **`pathinfo($file, PATHINFO_FILENAME)` on dotfiles** - Returns empty string for `.htaccess`
4. **PDO returns strings** - `entity_id` from DB is `"123"` not `123`. Cast before comparing or checksumming
5. **`str_replace()` cascading** - Replacement text may contain the next search pattern
6. **`random_bytes(float)`** - PHP 8.1+ deprecation warning; always pass int
7. **Validation in update methods** - Must match create/store validation (don't skip)

## File Counts

- 14 Controllers in `src/controllers/`
- 9 Models in `src/models/`
- 4 Services in `src/services/`
- 3 Helpers in `src/helpers/`
- 47 Views in `src/views/`
- 9 Core classes in `src/core/`
