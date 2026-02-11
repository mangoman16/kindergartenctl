# Bug Audit Report: Kindergarten Spiele Organizer

**Application:** Kindergarten Spiele Organizer v1.0.0
**Technology Stack:** PHP 8.0+, MySQL 8.x, Custom MVC Framework
**Audit Date:** 2026-02-11
**Status:** All bugs fixed

---

## Summary

| Severity | Found | Fixed |
|----------|-------|-------|
| Critical | 4 | 4 |
| High | 2 | 2 |
| Medium | 6 | 6 |
| Low | 1 | 1 |
| **Total** | **13** | **13** |

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

---

## Notes for Future Audits

- **Router::redirect() and Router::back()** both call `exit`, so `requireAuth()` and `requireCsrf()` in the base Controller DO halt execution. These were falsely flagged as bugs in earlier reviews.
- **Box::allWithMaterialCount()** validates sort direction with `strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC'`, so the `$direction` parameter from `BoxController` is NOT a SQL injection risk.
- The `InstallController` POST endpoints lack CSRF tokens, but the installer runs before any user account exists and is locked out after `installed.lock` is created. This is acceptable for the installation flow.
