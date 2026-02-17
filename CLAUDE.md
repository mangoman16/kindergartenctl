# Claude Instructions: KindergartenOrganizer

## Project Overview

A PHP 8.0+ web application for kindergarten teachers to organize educational games, materials, and storage boxes. Custom MVC framework (no Laravel/Symfony). Multi-language UI (German/English) with customization options.

## MD File Maintenance Instructions

When making changes to the codebase, **always** update the relevant MD files:
- **CLAUDE.md**: Update when architecture, patterns, file counts, or critical behaviors change
- **BUG_AUDIT.md**: Add entries when bugs are found/fixed (include file:line references)
- **SECURITY_AUDIT.md**: Update when security-related code is modified
- **CODE_QUALITY.md**: Update when code quality patterns change
- **todo.md**: Mark tasks complete, add new tasks discovered during implementation
- **README.md**: Update when install steps, dependencies, or config changes occur

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
src/lang/de.php           -> German translation strings (flat key=>value)
src/lang/en.php           -> English translation strings (flat key=>value)
```

## Critical Patterns to Follow

### 1. Redirects Always Exit
`Router::redirect()` and `Router::back()` both call `exit`. So `requireAuth()` and `requireCsrf()` DO halt execution. Do NOT add redundant `return` or `exit` after them.

### 2. PDO Emulated Prepares Are OFF
`ATTR_EMULATE_PREPARES => false` means you CANNOT reuse named parameters in a single query. Use distinct names (`:start1`, `:start2`, etc.) like `CalendarEvent::getForRange()` does.

### 3. Translation Function `__()`
- Supports multiple languages via `userPreference('language', 'de')`
- Language files: `src/lang/de.php` (German), `src/lang/en.php` (English)
- Keys use dot notation: `'game.title'`, `'auth.login'`
- Returns the key string itself if not found (no exception)
- Supports parameter replacement: `__('flash.created', ['item' => 'Spiel'])`
- `auth.logout` = "Abmelden" (nav button label)
- `auth.logged_out` = "Sie wurden abgemeldet." (flash message after logout)

### 3b. User Preferences System
- Preferences stored in `storage/preferences.php` (PHP array file)
- Access via `userPreference('key', 'default')` helper function
- Keys: `language`, `theme_color`, `theme_pattern`, `items_per_page`, `default_view`, `dark_mode_preference` (system|light|dark)
- SettingsController saves via `savePreferences()` method

### 3c. Debug Mode
- Toggle via Settings page (creates/removes `storage/debug.flag` file)
- App.php checks for flag file in `setupEnvironment()`
- When enabled: shows all PHP errors, SQL errors, and stack traces
- Config value `app.debug` is also respected (flag file overrides)

### 3d. Customization System
- Theme color: CSS custom property `--color-primary` overridden in layout
- Background patterns: `body[data-pattern="dots|stars|hearts|clouds"]` CSS patterns
- 8 preset colors available in settings

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

### 9. Navigation Structure (Asana-style)
- **Icon Rail** (56px fixed left): Sidebar toggle (hamburger) at top, then Home, Games, Inventory, Calendar buttons + Quick Create (plus) and Settings at bottom
- **Sidebar Toggle**: Hamburger button at top of icon rail (`#sidebarToggleBtn`). Collapses/expands context sidebar. State persisted in localStorage (`sidebarCollapsed`). Always visible across all pages.
- **Context Sidebar** (200px, slides in/out): Section-specific nav items. Games section → games, categories, tags, groups. Inventory section → materials, boxes, locations (Standorte). Calendar section has its own link. Changelog moved to settings.
- **Header**: Search trigger button (opens global command palette, Ctrl+K shortcut) + Help toggle + User dropdown (click username to open dropdown with Mein Konto, Einstellungen, Abmelden)
- **Help Panel** (380px right-side slide): Handbook-style guide with table of contents, auto-scrolls to current page guide
- **Quick Create Popup**: Accessible from plus button on icon rail, shortcuts to create games, materials, boxes, groups, calendar events
- **Dark Mode**: Settings in user settings page (`/user/settings`) with three buttons: System, Light, Dark. Persisted via AJAX POST to `/settings/dark-mode` as `dark_mode_preference` (system|light|dark). System mode uses `prefers-color-scheme` media query. CSS variables in `[data-theme="dark"]`
- **User settings** (`/user/settings`): Profile, language change, dark mode, password change, email change, user management (create/delete users)
- **App settings** (`/settings`): Menu with links to sub-pages: customization, language, email, debug, data, changelog, help wizard
- **Settings sub-pages**: `GET /settings/customization`, `/settings/language`, `/settings/email`, `/settings/debug`, `/settings/data`
- **Help wizard** (`/settings/help`): Step-by-step guided tour of the application
- **User management routes**: `POST /user/settings/language`, `POST /user/settings/create-user`, `POST /user/settings/delete-user`

### 10. Help System
- **Field tooltips**: `.help-tooltip` spans with `data-help` attribute on form labels
- **Category help**: Moved into the help panel as `.help-category-hint` divs under each section heading (no longer as banners on index pages)
- **Help wizard**: Step-by-step guide at `/settings/help`
- **Help panel**: Right-side sliding panel (`.help-panel`) with TOC and per-page guides. Toggle via header button. Auto-scrolls to current page section.
- Translation keys: `help.field_*` for field tooltips, `help.category_*` for category descriptions, `help.guide_*` for help panel guides

### 11. Search System
- **Command palette (Asana-style)**: Search trigger button in header opens a centered modal overlay (`.search-palette-overlay`). Keyboard shortcut: Ctrl+K / Cmd+K.
- **Fully global**: Search treats all entity types equally with balanced result limits (6 games, 6 materials, 4 boxes/tags/groups). No context-based prioritization.
- **Filter chips**: `.search-filter-chip` buttons in palette header to filter results by type (All, Games, Materials, Boxes, Tags, Groups). Shows result counts per type.
- **Search history**: Recent queries stored in localStorage (`searchHistory`, max 8). Shown when palette opens before typing.
- **Recently found**: Clicked results stored in localStorage (`searchRecent`, max 6). Shown below history on palette open.
- **Clear history**: Button in section header to clear all search history and recent items.
- **Keyboard navigation**: Arrow keys to navigate, Enter to open (or re-run history query), Escape to close.
- **Inline filters**: Per-page filters use `.inline-filters` with pill-style dropdowns and checkboxes (auto-submit on change). Filters are now **collapsible** behind a "Filter" toggle button with active filter count badge.

### 12. Dashboard Layout
- **Two-column layout**: Left column (Calendar, Recent Changes, Random Game) and Right column (Recent Games, Recently Played, Favorites)
- **Fixed widget sizes**: `.dash-card-fixed` with 340px height, overflow hidden
- **Predefined item limits**: 4 items per list widget (`array_slice($data, 0, 4)`)
- **Collapsible left column**: Toggle via `.dash-col-toggle` button, state persisted in localStorage (`dashLeftCollapsed`)

## File Counts

- 15 Controllers in `src/controllers/` (includes LocationController)
- 10 Models in `src/models/` (includes Location)
- 4 Services in `src/services/`
- 3 Helpers in `src/helpers/`
- 59 Views in `src/views/` (including user.php, help.php, help-panel.php, settings sub-pages, locations/)
- 9 Core classes in `src/core/`
- 2 Language files in `src/lang/`
