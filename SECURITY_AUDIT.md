# Security Audit Report: Kindergarten Spiele Organizer

**Audit Date:** 2026-01-08
**Auditor:** Automated Security Analysis
**Application:** Kindergarten Spiele Organizer v1.0.0
**Technology Stack:** PHP 8.0+, MySQL 8.x, Custom MVC Framework

---

## Executive Summary

This security audit covers the kindergartenctl codebase, a web application for kindergarten teachers to organize games and materials. The application is a single-user system with session-based authentication.

### Overall Security Posture: **MODERATE**

The application demonstrates good security practices in several areas but has vulnerabilities that should be addressed before production deployment.

| Category | Rating | Notes |
|----------|--------|-------|
| Authentication | Good | Bcrypt hashing, session regeneration, IP banning |
| SQL Injection | Good | Parameterized queries used consistently |
| XSS Prevention | Good | `e()` function used for output escaping |
| CSRF Protection | Good | Token validation on all POST requests |
| File Uploads | Good | MIME validation, image reprocessing |
| Configuration | Moderate | Debug mode enabled, secrets in files |
| Error Handling | Moderate | Some information leakage possible |

---

## Critical Vulnerabilities

### 1. ~~Password Field Name Mismatch~~ (FIXED - 2026-02-01)

**Location:** `src/controllers/SettingsController.php:154, 201`

**Status:** RESOLVED

The password verification logic now correctly uses `$user['password_hash']`:

```php
// Line 154 - FIXED
if (!password_verify($currentPassword, $user['password_hash'])) {

// Line 201 - FIXED
if (!password_verify($password, $user['password_hash'])) {
```

Password update and email change features now work correctly.

---

### 2. Debug Mode Enabled in Production Configuration (HIGH SEVERITY)

**Location:** `src/config/config.php:14`

```php
'debug' => true, // Set to false in production
```

**Impact:** When debug mode is enabled:
- Detailed error messages may expose sensitive information
- Stack traces could reveal internal paths and code structure
- Database query errors could expose table/column names

**Recommendation:** Create environment-based configuration or ensure debug is set to `false` before deployment.

---

## High Severity Issues

### 3. Potential SQL Injection via Order By Clause

**Location:** `src/models/Game.php:81`

```php
$sql = "SELECT g.*,
        ...
        ORDER BY g.{$orderBy} {$direction}";
```

**Issue:** While `$direction` is sanitized to only allow `ASC/DESC`, the `$orderBy` parameter is interpolated directly into the SQL query without validation against a whitelist.

**Impact:** An attacker could potentially manipulate URL parameters to inject SQL into the ORDER BY clause. However, exploitation is limited because PHP's routing validates parameter names.

**Recommendation:** Validate `$orderBy` against a whitelist of allowed column names:
```php
$allowedColumns = ['name', 'created_at', 'updated_at', ...];
if (!in_array($orderBy, $allowedColumns)) {
    $orderBy = 'name';
}
```

### 4. Open Redirect Vulnerability

**Location:** `src/core/Router.php:200-204`

```php
public static function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    self::redirect($referer);
}
```

**Issue:** The `HTTP_REFERER` header can be spoofed by attackers. If used after form submissions, this could redirect users to malicious sites.

**Impact:** Could be used in phishing attacks to redirect users after authentication.

**Recommendation:** Validate that the referer is from the same domain:
```php
$referer = $_SERVER['HTTP_REFERER'] ?? '/';
if (!str_starts_with($referer, App::baseUrl())) {
    $referer = '/';
}
self::redirect($referer);
```

---

## Medium Severity Issues

### 5. Database Credentials Stored in Plain Text File

**Location:** `src/config/database.php`

**Issue:** Database credentials are stored in a PHP file without encryption. While this is common practice, it poses a risk if:
- The webserver misconfiguration exposes PHP files
- Backups are not properly secured
- Source code is accidentally committed with credentials

**Recommendation:**
- Use environment variables instead of config files
- Ensure `.htaccess` or server config blocks access to config files
- Add `database.php` to `.gitignore` (it appears auto-generated)

### 6. SMTP Password Stored in Plain Text

**Location:** `storage/smtp.php`

**Issue:** SMTP credentials are stored in plain text in a PHP array.

**Recommendation:** Use environment variables or encrypted configuration storage.

### 7. Missing Content Security Policy (CSP) Header

**Location:** `public/.htaccess`

**Issue:** The application sets some security headers but lacks a Content Security Policy:
- X-Content-Type-Options ✓
- X-Frame-Options ✓
- X-XSS-Protection ✓
- Referrer-Policy ✓
- **Content-Security-Policy ✗**

**Recommendation:** Add CSP header to prevent XSS and data injection attacks:
```apache
Header set Content-Security-Policy "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;"
```

### 8. External CDN Dependencies

**Location:** `src/views/layouts/main.php:14-17, 51-52`

**Issue:** JavaScript and CSS are loaded from external CDNs without Subresource Integrity (SRI) hashes:
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
```

**Impact:** If the CDN is compromised, malicious code could be injected.

**Recommendation:** Add SRI hashes:
```html
<script src="..." integrity="sha384-..." crossorigin="anonymous"></script>
```

### 9. Rate Limit File Storage Vulnerability

**Location:** `src/helpers/security.php:297-320`

```php
function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds): bool
{
    $cacheFile = STORAGE_PATH . '/cache/rate_' . md5($key) . '.json';
    // ...
    file_put_contents($cacheFile, json_encode($data));
}
```

**Issue:** Rate limiting uses file-based storage which:
- May cause race conditions under high load
- Could fill disk space if not cleaned up
- Is vulnerable to timing attacks

**Recommendation:** Use database-based rate limiting or Redis for better reliability.

### 10. Missing HTTPS Enforcement

**Location:** `src/config/config.php:22`

```php
'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
```

**Issue:** The secure cookie flag depends on the current connection. If the site is accessed via HTTP, cookies won't be marked secure.

**Recommendation:**
- Enforce HTTPS at the server level
- Set `'secure' => true` explicitly in production

---

## Low Severity Issues

### 11. Session Cookie Name is Predictable

**Location:** `src/config/config.php:19`

```php
'name' => 'kindergarten_session',
```

**Issue:** A predictable session name can make it easier for attackers to identify the application framework.

**Recommendation:** Consider a less descriptive session name.

### 12. Password Reset Token Not Single-Use Before Expiry

**Location:** `src/core/Auth.php:216-228`

**Issue:** Password reset tokens are validated but can potentially be reused within the expiry window if the `used_at` field is not properly checked before marking as used.

**Current flow:**
1. Token is validated
2. Password is updated
3. Token is marked as used

**Risk:** Race condition could allow token reuse.

**Recommendation:** Use database transactions to atomically validate and invalidate tokens.

### 13. Changelog Contains Sensitive Data

**Location:** `src/services/ChangelogService.php`

**Issue:** The changelog stores full JSON diffs of changes, which could include sensitive information that persists even after the data is deleted.

**Recommendation:**
- Implement data retention policies
- Consider not logging certain sensitive fields
- Provide admin interface to purge old logs (partially implemented)

### 14. Missing Input Length Validation on Some Fields

**Location:** Various controllers

**Issue:** While the Validator class supports `max` length validation, not all form inputs enforce maximum lengths server-side.

**Example:** Tag name validation in `ApiController.php:398-401` checks max length, but other controllers may not consistently apply these checks.

**Recommendation:** Ensure all user inputs have length limits matching database column sizes.

### 15. Potential Information Disclosure in Error Messages

**Location:** `src/core/Database.php:86-87`

```php
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    throw new Exception('Datenbankverbindung fehlgeschlagen');
}
```

**Assessment:** This is handled correctly - the generic message is thrown while details are logged. However, ensure `error_log` output is not accessible via web.

---

## Positive Security Findings

### 1. Password Hashing
- Uses `PASSWORD_DEFAULT` (bcrypt) with automatic cost factor
- Proper use of `password_verify()` function
- Passwords never logged or stored in plain text

### 2. SQL Injection Prevention
- PDO with prepared statements throughout
- `PDO::ATTR_EMULATE_PREPARES => false` enforces real prepared statements
- No string concatenation in queries for user input

### 3. CSRF Protection
- Tokens generated with `random_bytes(32)`
- Token validation using `hash_equals()` (timing-safe comparison)
- Tokens regenerated periodically (1-hour lifetime)

### 4. XSS Prevention
- `e()` helper function using `htmlspecialchars()` with `ENT_QUOTES`
- Output escaping used consistently in views
- `old()` function auto-escapes values

### 5. Session Security
- Session regeneration on login
- Periodic session ID regeneration (every 30 minutes)
- HTTPOnly cookies enabled
- SameSite=Lax cookie attribute

### 6. File Upload Security
- MIME type validation using `finfo`
- Image reprocessing (converts all uploads to WebP)
- Secure filename generation with random bytes
- Size limits enforced
- Suspicious content detection (PHP, JavaScript)

### 7. IP-Based Brute Force Protection
- Failed login tracking per IP
- Temporary ban after 5 failed attempts
- Permanent ban after 10 failed attempts
- Cloudflare IP detection support

### 8. Remember Me Security
- Tokens hashed with SHA-256 before storage
- Token expiration enforced
- Tokens cleared on logout

---

## Recommendations Summary

### Immediate Actions (Critical/High):
1. ~~Fix password field name in SettingsController.php~~ **FIXED 2026-02-01**
2. Disable debug mode for production
3. ~~Whitelist ORDER BY columns~~ **FIXED** (all models now use $allowedOrderColumns)
4. Validate redirect URLs (Router::back() open redirect)
5. ~~Fix ApiController auth bypass via str_contains~~ **FIXED 2026-02-06**
6. ~~Fix unvalidated image_path in all controllers~~ **FIXED 2026-02-06**

### Short-Term Actions (Medium):
7. Add Content-Security-Policy header
8. Add SRI hashes to CDN resources
9. Move secrets to environment variables
10. Enforce HTTPS
11. ~~Fix CalendarEvent/Material reused PDO params~~ **FIXED 2026-02-06**
12. ~~Fix Game::updateTags/updateMaterials transaction safety~~ **FIXED 2026-02-06**
13. ~~Remove password_hash/remember_token from User $fillable~~ **FIXED 2026-02-06**

### Long-Term Actions (Low):
14. Implement proper rate limiting backend
15. Review changelog data retention
16. Consider session token name
17. Add comprehensive input validation

---

## Additional Security Reviews

Detailed follow-up audits are documented in separate files:
- `SECURITY_AUDIT_2026-01-16.md` - Comprehensive security analysis
- `SECURITY_AUDIT_2026-01-16_COMPREHENSIVE.md` - Deep-dive security review
- `SECURITY_AUDIT_2026-01-16_UPDATE.md` - Security fixes applied on 2026-01-16
- `BUG_AUDIT_REPORT.md` - Bug tracking and fixes

---

## Files Reviewed

| File | Lines | Issues Found |
|------|-------|--------------|
| src/core/Auth.php | 241 | 0 |
| src/core/Session.php | 241 | 0 |
| src/core/Database.php | 468 | 0 |
| src/core/Controller.php | 263 | 0 |
| src/core/Model.php | 345 | 1 (ORDER BY) |
| src/core/Router.php | 206 | 1 (Open redirect) |
| src/core/Validator.php | 309 | 0 |
| src/helpers/security.php | 321 | 1 (Rate limit) |
| src/helpers/functions.php | 362 | 0 |
| src/controllers/AuthController.php | 220 | 0 |
| src/controllers/ApiController.php | 780 | 0 |
| src/controllers/GameController.php | 414 | 0 |
| src/controllers/SettingsController.php | 415 | 1 (Critical bug) |
| src/controllers/InstallController.php | 357 | 0 |
| src/services/ImageProcessor.php | 421 | 0 |
| src/services/Mailer.php | 440 | 0 |
| src/config/config.php | 53 | 1 (Debug mode) |
| public/.htaccess | 62 | 1 (Missing CSP) |
| src/views/layouts/main.php | 56 | 1 (No SRI) |
| src/views/games/*.php | Multiple | 0 (Good escaping) |

---

## Conclusion

The Kindergarten Spiele Organizer demonstrates good security fundamentals with proper use of prepared statements, password hashing, CSRF protection, and XSS prevention. All critical security bugs have been resolved as of 2026-02-06.

**Remaining items before production deployment:**
- Disable debug mode (`config.php` -> `'debug' => false`)
- Validate redirect URLs in Router::back()
- Add Content-Security-Policy header
- Add SRI hashes to CDN resources
- Enforce HTTPS

For a single-user application with limited exposure, the current security posture is acceptable. For broader deployment, the medium-severity recommendations should also be implemented.
