# Bug Audit Report: Kindergarten Spiele Organizer

**Application:** Kindergarten Spiele Organizer v1.0.0
**Technology Stack:** PHP 8.0+, MySQL 8.x, Custom MVC Framework
**Audit Date:** 2026-02-11
**Status:** All bugs fixed

---

## Summary

| Severity | Found | Fixed |
|----------|-------|-------|
| Critical | 6 | 6 |
| High | 5 | 5 |
| Medium | 12 | 12 |
| Low | 2 | 2 |
| **Total** | **25** | **25** |

---

## Fixed Bugs

### BUG-13 (Critical): `CalendarController::json()` Access Level Fatal Error
- **File:** `src/controllers/CalendarController.php:295`
- **Problem:** The `CalendarController` declared a `private function json()` method, but the parent `Controller` class already has `protected function json()`. PHP forbids narrowing method visibility in child classes, causing a fatal error when opening the Calendar page.
- **Fix:** Removed the duplicate `private json()` method from `CalendarController`, allowing it to inherit the `protected json()` from the parent `Controller` class. The private `jsonError()` helper was retained.

### BUG-01 (Critical): `formatDate()` M-to-F Replacement Corruption
- **File:** `src/helpers/dates.php:132-143`
- **Problem:** When replacing `M` with German short month names, the result (e.g., "Feb.") introduces an `F` character. The subsequent `F` replacement then corrupts the output. Every February date was affected.
- **Fix:** Reversed the replacement order -- `F` is now replaced before `M`, preventing cascading substitution.

### BUG-02 (Critical): `verifyTransaction()` Checksum Always Fails
- **File:** `src/services/TransactionService.php:269`
- **Problem:** `entity_id` is stored as `int` during `logTransaction()` but read back as `string` from PDO. `json_encode(123)` != `json_encode("123")`, so the SHA-256 checksum never matches. All transaction verifications were reported as failed.
- **Fix:** Cast `entity_id` to `(int)` (or null) in `verifyTransaction()` before checksum calculation.

### BUG-03 (Critical): InstallController Stores SMTP Password in Plaintext
- **File:** `src/controllers/InstallController.php:237`
- **Problem:** During installation, the SMTP password was saved as plaintext. `SettingsController` saves it encrypted. The `Mailer` uses `decryptValue()`, which treats plaintext as a legacy value, but this inconsistency was a security risk.
- **Fix:** Added `encryptValue()` call in `InstallController::saveEmail()` to match `SettingsController` behavior.

### BUG-04 (High): `sanitizeFilename()` Dangerous File Check Broken
- **File:** `src/helpers/security.php:220-241`
- **Problem:** `pathinfo('.htaccess', PATHINFO_FILENAME)` returns `""` (empty), not `.htaccess`. Similarly, `pathinfo('config.php', PATHINFO_FILENAME)` returns `config`, not `config.php`. So dotfiles (`.htaccess`, `.env`, `.htpasswd`) and files with extensions (`config.php`, `database.php`, `web.config`) were never blocked.
- **Fix:** Split the list into extensionless names (checked against `PATHINFO_FILENAME`) and full-name entries (checked against the full lowercase filename).

### BUG-05 (High): `encryptValue()` Silently Returns Plaintext on Failure
- **File:** `src/helpers/security.php:490-491`
- **Problem:** If `openssl_encrypt()` fails, the function returned the original plaintext value without any indication. Sensitive data (e.g., SMTP passwords) could be stored unencrypted.
- **Fix:** Replaced plaintext fallback with `throw new RuntimeException()` and error logging.

### BUG-06 (Medium): `formatTimeAgo()` Singular Forms Never Used
- **File:** `src/helpers/dates.php:234-250`
- **Problem:** `floor()` returns a `float` (e.g., `1.0`). The strict comparison `=== 1` (int) is always `false`. So "vor 1 Minuten" was shown instead of "vor 1 Minute".
- **Fix:** Added `(int)` cast to all `floor()` results.

### BUG-07 (Medium): Duplicate `auth.logout` Translation Key
- **File:** `src/lang/de.php:192,211`
- **Problem:** `auth.logout` was defined twice: first as "Abmelden" (button label), then as "Sie wurden abgemeldet." (flash message). The second silently overwrote the first. Any navigation logout button displayed the flash message text.
- **Fix:** Renamed the second occurrence to `auth.logged_out` and updated `AuthController.php` to use the new key.

### BUG-08 (Medium): `randomString()` Fails on Odd Lengths
- **File:** `src/helpers/functions.php:251`
- **Problem:** `$length / 2` for odd numbers produces a float. `random_bytes()` in PHP 8.1+ emits a deprecation warning for floats, and the returned string is 1 char shorter than requested.
- **Fix:** Used `(int)ceil($length / 2)` and `substr()` to get exact requested length.

### BUG-09 (Medium): `__()` Translation Function Returns Null on Null Input
- **File:** `src/helpers/functions.php:54,67`
- **Problem:** Parameter is `?string` (nullable), return type is `string` (non-nullable). Passing `null` returns `null`, violating the return type and causing a `TypeError` in strict mode.
- **Fix:** Added early `null` check returning empty string.

### BUG-10 (Medium): CalendarController::update() Has No Input Validation
- **File:** `src/controllers/CalendarController.php:170-213`
- **Problem:** The `store()` method validated title, description length, date formats, and color. The `update()` method performed none of these checks, accepting arbitrary data.
- **Fix:** Added the same validation logic from `store()` to `update()`.

### BUG-11 (Medium): Missing Translation Keys for ImageProcessor
- **File:** `src/services/ImageProcessor.php` + `src/lang/de.php`
- **Problem:** `validation.file_too_large` and `validation.invalid_image` were used in code but not defined in the language file. Users saw raw key strings.
- **Fix:** Added both keys to `de.php`.

### BUG-12 (Low): `parseGermanDate()` Accepts Impossible Dates
- **File:** `src/helpers/dates.php:379-400`
- **Problem:** `DateTime::createFromFormat('d.m.Y', '31.02.2026')` silently overflows to March 3rd instead of returning an error.
- **Fix:** Added `DateTime::getLastErrors()` check to reject dates with warnings or errors.

### BUG-14 (Medium): Background Pattern Not Applied
- **File:** `src/views/layouts/main.php` (inline `<style>`)
- **Problem:** An inline `<style>` block set `background-image: var(--pattern-bg)` on `.page-content`, but `--pattern-bg` was never defined as a CSS variable. This inline style overrode the actual pattern CSS selectors (`body[data-pattern="dots"] .page-content`, etc.) in style.css, so no pattern was ever visible.
- **Fix:** Removed the conflicting inline style block from main.php.

### BUG-15 (Medium): SMTP Test Link Sends GET to POST-Only Route (404)
- **File:** `src/views/settings/index.php` (old version)
- **Problem:** The "Test SMTP" button was an `<a href>` link, sending a GET request to `/settings/smtp/test`. The route was defined as POST only, resulting in a 404 error.
- **Fix:** Replaced with a `<form method="POST">` including CSRF token and a test email address input field.

### BUG-17 (Medium): Ideas Table Missing `user_id` — Not Associated with Users
- **File:** `src/core/Database.php:665-681` (schema DDL) and `src/core/Database.php:716-732` (migration)
- **Problem:** The `ideas` table had no `user_id` column. Ideas were stored globally without any user association, meaning all users shared the same ideas pool. The table was also miscategorized in the schema docblock alongside security tables (`ip_bans`).
- **Fix:** Added `user_id INT UNSIGNED NOT NULL` column with `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` to both the schema DDL and the migration's `CREATE TABLE`. Added an `ALTER TABLE` migration for existing installs. Updated the schema docblock to list ideas as a separate category ("per-user idea tracking").

### BUG-16 (Low): Search Palette Incomplete Dark Mode Styling
- **File:** `public/assets/css/style.css:269-335`
- **Problem:** The search command palette (`.search-palette`) had only partial dark mode overrides. Missing styles included: palette background color (appeared too dark against overlay), header/footer border colors (invisible `--color-gray-100` borders), placeholder text color (too dim), hint/empty state text colors, result item hover/active backgrounds (insufficient contrast), item type labels, and the "more results" link styling. The palette was barely distinguishable from the dark overlay backdrop.
- **Fix:** Added comprehensive `[data-theme="dark"]` overrides for all search palette elements: elevated background (`--color-gray-100`), stronger box-shadow, visible border colors (`--color-gray-300`), proper text contrast for all child elements, and hover/active states with adequate contrast (`--color-gray-200`).

### BUG-18 (Critical): Image Upload Cropping Stuck on "Bild zuschneiden"
- **File:** `public/assets/js/app.js:248-269`
- **Problem:** `canvas.toBlob('image/webp')` silently fails in browsers lacking WebP encoding support (e.g. older Safari). The callback never fires, leaving the crop modal frozen.
- **Fix:** Added WebP→JPEG fallback chain in `tryBlob()`. Added error handling so the modal never freezes. Also fixed hardcoded `image.webp` filename to match the actual blob MIME type.

### BUG-19 (Critical): Calendar Quick-Create 404
- **File:** `src/views/partials/sidebar.php:76`
- **Problem:** Quick-create "Eintrag hinzufügen" linked to `/calendar/create` which doesn't exist as a route. Calendar events are created via modal on `/calendar`.
- **Fix:** Changed link to `/calendar?create=1`. Added auto-open logic in `calendar/index.php` to detect `?create=1` and open the create modal automatically.

### BUG-20 (High): Design/Customization Settings Not Persisting Visually
- **File:** `src/views/settings/customization.php:97-115`
- **Problem:** Saving theme color/pattern required 4-5 clicks due to browser caching the old inline CSS variables in the HTML.
- **Fix:** Converted form submit to AJAX. CSS variables are updated immediately in the browser via `document.documentElement.style.setProperty()` on save, providing instant visual feedback without page reload.

### BUG-21 (High): Game Detail View — Wrong Location Data
- **File:** `src/models/Game.php:136-143`
- **Problem:** `Game::findWithRelations()` used `b.location` (old varchar field) instead of joining the `locations` table via `b.location_id`.
- **Fix:** Updated query to `LEFT JOIN locations l ON l.id = b.location_id` with `COALESCE(l.name, b.location) as box_location`.

### BUG-22 (High): Game Detail Modal Backdrop Not Covering Screen
- **File:** `src/views/games/show.php:430`
- **Problem:** Modal backdrop used `position: absolute` instead of `fixed`, and hardcoded `background: white` broke dark mode.
- **Fix:** Changed to `position: fixed` on backdrop, scoped CSS selectors to `#add-to-group-modal` to avoid conflicts, replaced `background: white` with `var(--color-white)`.

### BUG-23 (Medium): Missing `$fillable` for `notes` in 6 Models
- **Files:** `Game.php:34`, `CalendarEvent.php:19`, `Category.php:20`, `Tag.php:19`, `Group.php:22`, `Location.php:9`
- **Problem:** Database migration adds `notes TEXT NULL` columns to these tables, but models lacked `notes` in their `$fillable` arrays. CalendarEvent also lacked `event_type`.
- **Fix:** Added `'notes'` to all 6 model `$fillable` arrays, and `'event_type'` to CalendarEvent.

### BUG-24 (Medium): Undefined CSS Variable `--color-yellow-100`
- **File:** `public/assets/css/style.css:1106`
- **Problem:** `<mark>` highlight used `var(--color-yellow-100)` but this variable was never defined in `:root`.
- **Fix:** Added `--color-yellow-100: #FEF9C3;` to `:root` variables.

### BUG-25 (Medium): Search Was Context-Dependent Instead of Global
- **Files:** `src/views/partials/header.php`, `src/controllers/ApiController.php:377-489`
- **Problem:** Search prioritized results based on current page section (games page → more games, materials page → more materials). User expected fully global search.
- **Fix:** Removed context detection. Equalized API result limits across all entity types. Added filter chips, search history (localStorage), and recently found items to the search palette.

---

## Notes for Future Audits

- **Router::redirect() and Router::back()** both call `exit`, so `requireAuth()` and `requireCsrf()` in the base Controller DO halt execution. These were falsely flagged as bugs in earlier reviews.
- **Box::allWithMaterialCount()** validates sort direction with `strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC'`, so the `$direction` parameter from `BoxController` is NOT a SQL injection risk.
- The `InstallController` POST endpoints lack CSRF tokens, but the installer runs before any user account exists and is locked out after `installed.lock` is created. This is acceptable for the installation flow.
