# Bug Audit Report: Kindergarten Spiele Organizer

**Application:** Kindergarten Spiele Organizer v1.0.0
**Technology Stack:** PHP 8.0+, MySQL 8.x, Custom MVC Framework
**Audit Date:** 2026-02-11
**Status:** All bugs fixed

---

## Summary

| Severity | Found | Fixed |
|----------|-------|-------|
| Critical | 10 | 10 |
| High | 6 | 6 |
| Medium | 30 | 30 |
| Low | 12 | 12 |
| **Total** | **58** | **58** |

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

### BUG-26 (Critical): Duplicate Check API Sends POST to GET-Only Route
- **File:** `public/assets/js/app.js:325`
- **Problem:** JavaScript sent `POST /api/{type}/check-duplicate` with JSON body, but the route is `GET /api/check-duplicate` and the controller reads query params via `$this->getQuery()`. Duplicate name checking silently failed on all forms.
- **Fix:** Changed to `GET /api/check-duplicate?type=...&value=...&exclude_id=...` with proper query parameters.

### BUG-27 (Medium): Missing `response.ok` Checks in Fetch Calls
- **Files:** `src/views/games/show.php:314`, `src/views/materials/show.php:203`
- **Problem:** Fetch calls went straight to `.json()` without checking HTTP status. 4xx/5xx errors caused silent JSON parse failures.
- **Fix:** Added `if (!response.ok) throw new Error('HTTP ' + response.status)` before `.json()` in all fetch calls.

### BUG-28 (Medium): Duplicate Dark Mode CSS Rule for Search Mark
- **File:** `public/assets/css/style.css:390-392`
- **Problem:** `.search-palette-item-name mark` had two conflicting dark mode overrides with different highlight colors (rgb 245,158,11 vs 234,179,8). Second silently overrode first.
- **Fix:** Removed duplicate rule, kept the first (consistent with light mode).

### BUG-29 (Medium): Missing Dark Mode Overrides for Major UI Components
- **File:** `public/assets/css/style.css`
- **Problem:** Icon rail, context sidebar, top header, user dropdown, help panel, card footer, form labels, breadcrumbs, pagination, and modals all lacked dark mode overrides, making them unreadable.
- **Fix:** Added `[data-theme="dark"]` overrides for all affected components.

### BUG-30 (Medium): No Mobile Breakpoint Below 480px
- **File:** `public/assets/css/style.css`
- **Problem:** Only breakpoints at 1200px, 992px, and 768px existed. Small phones (<480px) had oversized padding, cramped layouts, and overflowing elements.
- **Fix:** Added `@media (max-width: 480px)` breakpoint reducing padding, grid columns, and min-widths.

### BUG-31 (Medium): Hundreds of Hardcoded German Strings Across Views
- **Files:** `src/views/changelog/index.php`, `src/views/calendar/index.php`, `src/views/materials/index.php`, `src/views/games/index.php`, `src/views/boxes/index.php`, `src/views/categories/index.php`, `src/views/tags/index.php`, `src/views/groups/index.php`, `src/views/materials/show.php`, `src/views/dashboard/index.php`
- **Problem:** Filter labels, empty states, modal titles/buttons, bulk action labels, badge text, error messages, and pagination text were hardcoded in German instead of using `__()` translation function.
- **Fix:** Added 80+ new translation keys to `src/lang/de.php` and `src/lang/en.php`. Updated all affected views to use `__()` calls.

### BUG-32 (Critical): Frontend XSS via innerHTML with Unescaped API Data
- **Files:** `src/views/games/index.php:547`, `src/views/materials/index.php:355`, `src/views/games/form.php:283-294`, `src/views/groups/form.php:199,239`, `src/views/changelog/index.php:299-310`
- **Problem:** Multiple views used `innerHTML` with unescaped data from API responses or select options. Group names, material names, game names, and changelog field/values were injected directly into innerHTML template literals without sanitization, allowing stored XSS.
- **Fix:** Replaced innerHTML with `createElement`/`textContent` (games/index, materials/index) and added `escHtml()` / `esc()` helper functions that use `textContent`→`innerHTML` escaping technique (games/form, groups/form, changelog/index).

### BUG-33 (High): Missing Error Handling in Bulk Group Loading
- **Files:** `src/views/games/index.php:541-553`, `src/views/materials/index.php:349-361`
- **Problem:** The bulk "add to group" feature fetched groups from `/api/groups` without any error handling. No `response.ok` check, no try/catch around the fetch, and no validation of the response data structure. Network failures or server errors would cause silent JSON parse exceptions.
- **Fix:** Wrapped in try/catch, added `response.ok` check, validated response structure with `Array.isArray()`, and added property existence checks before DOM insertion.

### BUG-34 (Medium): Missing Null Check in Games Form Material Select
- **File:** `src/views/games/form.php:264-267`
- **Problem:** `addMaterialSelect.addEventListener('change', ...)` was called without checking if the element exists. On pages where the `#add-material` select is absent, this throws a TypeError.
- **Fix:** Added `if (!addMaterialSelect || !materialsList) return;` guard before attaching event listener.

### BUG-35 (Medium): Race Condition in Image Crop toBlob Callback
- **File:** `public/assets/js/app.js:275-288`
- **Problem:** `canvas.toBlob()` could fail to call its callback in rare edge cases (browser bugs, memory pressure), leaving the crop modal UI frozen with a disabled "Apply" button indefinitely.
- **Fix:** Added a 10-second timeout that re-enables the button and shows an error message if the blob callback never fires.

### BUG-36 (Medium): Z-index Conflict Between Search Palette and Cropper Modal
- **File:** `public/assets/css/style.css`
- **Problem:** Search palette overlay had `z-index: 9999` while cropper modal had `z-index: 2000`. If search palette was open when triggering image crop, the search overlay would block the cropper.
- **Fix:** Added `.cropper-modal-overlay { z-index: 10000 !important; }` to ensure cropper always appears above search palette.

### BUG-37 (Low): Missing Dark Mode Styles for `.btn-secondary`
- **File:** `public/assets/css/style.css`
- **Problem:** Secondary buttons had no `[data-theme="dark"]` overrides, making them nearly invisible or poorly contrasted in dark mode (e.g., the cropper modal's Cancel button).
- **Fix:** Added dark mode styles with `--color-gray-700` background and `--color-gray-100` text color.

### BUG-38 (Low): Missing Focus State for Search Trigger Button
- **File:** `public/assets/css/style.css`
- **Problem:** The `.search-trigger` button had hover styles but no `:focus` state, making keyboard navigation invisible. Violates WCAG 2.1 focus indicator requirement.
- **Fix:** Added `.search-trigger:focus` with a 2px primary-color outline.

### BUG-39 (Critical): `Session::getCsrfToken()` Fatal Error on Calendar Page
- **File:** `src/views/calendar/index.php:356`
- **Problem:** View called `Session::getCsrfToken()` which does not exist. The correct method is `Session::csrfToken()`. This caused a fatal error every time the calendar page was loaded.
- **Fix:** Replaced `Session::getCsrfToken()` with `$csrfToken` (already available in views via `Controller::render()`).

### BUG-40 (Critical): `ApiController::json()` Access Level Fatal Error
- **File:** `src/controllers/ApiController.php:850`
- **Problem:** `ApiController` declared `private function json()`, but the parent `Controller` has `protected function json()`. PHP forbids narrowing visibility in child classes, causing a fatal error on any API request.
- **Fix:** Changed `private function json()` to `protected function json()` to match parent visibility.

### BUG-41 (Medium): Undefined `csrf()` Function in Game/Material Views
- **Files:** `src/views/materials/show.php:197,233`, `src/views/games/show.php:308,345`
- **Problem:** Views called `csrf()` which does not exist as a function. The available helpers are `csrfField()` (returns full HTML input) and `Session::csrfToken()` (returns token string). The `$csrfToken` variable is also available in views. This caused fatal errors when toggling favorites or adding to groups.
- **Fix:** Replaced `csrf()` with `e($csrfToken)` which outputs the escaped CSRF token value.

### BUG-42 (Medium): DashboardController Missing `requireAuth()` in Constructor
- **File:** `src/controllers/DashboardController.php:6-17`
- **Problem:** Unlike all other protected controllers, `DashboardController` did not call `$this->requireAuth()` in its constructor. Instead, it performed a manual `Auth::check()` inside `index()` with a redundant `return` after `$this->redirect()` (which calls `exit`). This violated the established pattern and created a maintenance risk if methods were added later.
- **Fix:** Added proper `__construct()` method calling `$this->requireAuth()`, removed the manual auth check and dead `return` statement from `index()`.

### BUG-43 (Medium): Search Fetch Missing `response.ok` Check
- **File:** `src/views/partials/header.php:435`
- **Problem:** The search palette's `fetch()` call went straight to `.json()` without checking HTTP status. A 4xx/5xx response (e.g., rate-limited, server error) would cause a JSON parse exception silently swallowed by the empty `.catch()`.
- **Fix:** Added `if (!r.ok) throw new Error('HTTP ' + r.status)` before `.json()`.

### BUG-44 (Low): SMTP `getResponse()` Does Not Detect Read Timeout
- **File:** `src/services/Mailer.php:425-435`
- **Problem:** `fgets()` returns `false` on both EOF and socket timeout. The loop exited silently without distinguishing between the two, potentially returning a truncated SMTP response that could cause protocol violations.
- **Fix:** Added `stream_get_meta_data()` check after the loop to detect and log timeout conditions.

### BUG-45 (Medium): Search Input Focus States Missing (WCAG Violation)
- **File:** `public/assets/css/style.css:928,1319`
- **Problem:** `.search-palette-header input:focus` and `.search-form input:focus` had `outline: none` with no replacement focus indicator, making keyboard navigation inaccessible.
- **Fix:** Added `:focus-visible` rules with `outline: 2px solid var(--color-primary-light)` and `outline-offset: 2px`.

### BUG-46 (Low): Touch Targets Below 44px Minimum
- **File:** `public/assets/css/style.css:632-634,821-822`
- **Problem:** `.rail-btn` (40px) and `.header-icon-btn` (36px) were below the 44px WCAG minimum touch target size.
- **Fix:** Increased both to 44px width and height.

### BUG-47 (Low): Missing `prefers-reduced-motion` Support
- **File:** `public/assets/css/style.css`
- **Problem:** No `@media (prefers-reduced-motion: reduce)` block existed. Users with vestibular disorders or motion sensitivity had no way to disable animations.
- **Fix:** Added global reduced-motion media query that sets all animation/transition durations to 0.01ms.

### BUG-48 (Low): Hardcoded Values Instead of Design Tokens
- **File:** `public/assets/css/style.css` (multiple locations)
- **Problem:** ~40 instances of hardcoded px/rem values for spacing, shadows, border-radius, font-sizes, and transitions instead of using CSS custom property tokens. Creates maintenance burden and inconsistency.
- **Fix:** Replaced all hardcoded values with corresponding `--spacing-*`, `--shadow-*`, `--radius-*`, `--font-size-*`, and `--transition-*` tokens.

### BUG-49 (Medium): All buttons lack `:focus-visible` states
- **File:** `public/assets/css/style.css:1893-1896`
- **Problem:** No `.btn:focus-visible` rule existed. Keyboard-only users had no visual focus indicator when tabbing through buttons. WCAG 2.4.7 violation.
- **Fix:** Added `.btn:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }` cascading to all button variants.

### BUG-50 (Medium): Buttons lack `:active` state feedback
- **File:** `public/assets/css/style.css:1893-1896`
- **Problem:** No tactile feedback when clicking buttons — no visual change between hover and mousedown.
- **Fix:** Added `.btn:active:not(:disabled) { transform: scale(0.97); }` for subtle press feedback.

### BUG-51 (Medium): `.form-select.is-invalid` styling missing
- **File:** `public/assets/css/style.css:2055-2069`
- **Problem:** `.form-control.is-invalid` had red border styling but `.form-select.is-invalid` did not. Invalid select dropdowns showed no visual error indicator.
- **Fix:** Added matching `.form-select.is-invalid` and `:focus` rules with danger border and box-shadow.

### BUG-52 (Medium): Missing `:disabled` styling for form inputs
- **File:** `public/assets/css/style.css`
- **Problem:** No visual distinction for disabled form controls. Users couldn't tell which fields were interactive vs read-only.
- **Fix:** Added `.form-control:disabled, .form-select:disabled` with gray background, muted text color, and not-allowed cursor. Added dark mode variant.

### BUG-53 (Medium): Pagination and table-sort links lack focus indicators
- **File:** `public/assets/css/style.css:2319,3039`
- **Problem:** `.pagination-link` and `.table-sort` had no `:focus-visible` rules, invisible to keyboard navigation.
- **Fix:** Added `:focus-visible` with consistent outline pattern matching buttons.

### BUG-54 (Medium): Z-index conflicts between dropdowns, user menu, and modals
- **File:** `public/assets/css/style.css:1350,1531,2523,2541`
- **Problem:** `.search-dropdown`, `.user-dropdown`, `.modal-overlay`, and `.modal` all shared `z-index: 1000`. Overlapping elements could appear behind each other.
- **Fix:** Staggered z-indices: search-dropdown: 1000, user-dropdown: 1010, modals: 1020. Removed duplicate cropper z-index override.

### BUG-55 (Low): Inconsistent card-body padding removal across views
- **Files:** 11 view files across categories, groups, materials, search, games, changelog, calendar
- **Problem:** Three different patterns for zero-padding card bodies: `style="padding: 0;"`, `class="p-0"`, inline spacing tokens. No canonical CSS class.
- **Fix:** Added `.card-body-flush` utility class. Updated all 11 occurrences across 8 view files.

### BUG-56 (Low): Inconsistent toggle selection button ID and translation key
- **File:** `src/views/materials/index.php:4,9,213`
- **Problem:** Materials used `toggle-select-mode` + `action.select` while Games used `toggle-selection-mode` + `bulk.multi_select`. Same feature, different identifiers.
- **Fix:** Standardized Materials to match Games: ID `toggle-selection-mode`, key `bulk.multi_select`.

### BUG-57 (Low): Hardcoded German text in views
- **Files:** `src/views/locations/index.php:18`, `src/views/categories/index.php:50`
- **Problem:** "Sortieren nach:" and "Spiele" were hardcoded German strings instead of translation function calls.
- **Fix:** Replaced with `__('action.sort_by')` and `__('nav.games')`. Added `action.sort_by` key to both language files.

### BUG-58 (Low): Modal backdrop opacity inconsistency
- **File:** `public/assets/css/style.css:2548`
- **Problem:** `.modal-backdrop` used `rgba(0,0,0,0.45)` while `.modal-overlay` used `rgba(0,0,0,0.5)`. Different backdrop darkness for the same purpose.
- **Fix:** Standardized to `rgba(0,0,0,0.5)`.

---

## Notes for Future Audits

- **Router::redirect() and Router::back()** both call `exit`, so `requireAuth()` and `requireCsrf()` in the base Controller DO halt execution. These were falsely flagged as bugs in earlier reviews.
- **Box::allWithMaterialCount()** validates sort direction with `strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC'`, so the `$direction` parameter from `BoxController` is NOT a SQL injection risk.
- The `InstallController` POST endpoints lack CSRF tokens, but the installer runs before any user account exists and is locked out after `installed.lock` is created. This is acceptable for the installation flow.
