<?php
/**
 * =====================================================================================
 * SECURITY HELPERS - CSRF, Rate Limiting, and Security Utilities
 * =====================================================================================
 *
 * PURPOSE:
 * Security-focused utility functions for CSRF protection, rate limiting,
 * and token generation. Autoloaded globally via composer.json.
 *
 * FUNCTION INDEX:
 * - generateCsrfToken()    : Create and store a CSRF token in the session
 * - verifyCsrfToken($tok)  : Validate a submitted CSRF token (timing-safe compare)
 * - csrfField()            : Generate HTML hidden input with current CSRF token
 * - generateSecureToken()  : Create a cryptographically secure random hex token
 * - hashToken($token)      : SHA-256 hash a token for storage (password resets, etc.)
 * - checkRateLimit($key)   : File-based rate limiter (returns false if over limit)
 * - sanitizeFilename($name): Remove unsafe characters from filenames
 *
 * CSRF PROTECTION:
 * - Token stored in $_SESSION['csrf_token']
 * - All state-changing forms include csrfField() in their HTML
 * - Controllers call $this->requireCsrf() which calls verifyCsrfToken()
 * - Uses hash_equals() for timing-safe comparison to prevent timing attacks
 *
 * RATE LIMITING:
 * - File-based (storage/cache/ratelimit_*.json) rather than DB-based
 * - Tracks attempts per key (e.g., "login:192.168.1.1") with timestamps
 * - Window-based: counts attempts within the last N seconds
 *
 * AI NOTES:
 * - CSRF tokens are per-session, not per-request (single token reused)
 * - Rate limit files are JSON with structure: {"attempts": [timestamp, ...]}
 * - generateSecureToken() uses random_bytes() (CSPRNG)
 * - hashToken() is used for password reset tokens: plain token emailed to user,
 *   hashed version stored in DB. On verification, submitted token is hashed
 *   and compared to stored hash.
 *
 * RELATED FILES:
 * - src/core/Controller.php - requireCsrf() method
 * - src/core/Auth.php - Uses rate limiting for login attempts
 * - src/controllers/AuthController.php - Password reset token flow
 * - storage/cache/ - Rate limit files stored here
 *
 * @package KindergartenOrganizer\Helpers
 * @since 1.0.0
 * =====================================================================================
 */

/**
 * Get list of trusted proxy IP addresses
 * In production, configure this in config/config.php as 'trusted_proxies'
 */
function getTrustedProxies(): array
{
    $config = App::config('security', []);
    return $config['trusted_proxies'] ?? ['127.0.0.1', '::1'];
}

/**
 * Get the client IP address
 * Only trusts proxy headers when request comes from a trusted proxy
 */
function getClientIp(): string
{
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $trustedProxies = getTrustedProxies();

    // Only check proxy headers if the request is from a trusted proxy
    // This prevents IP spoofing attacks
    if (!in_array($remoteAddr, $trustedProxies, true)) {
        return $remoteAddr;
    }

    // Request is from trusted proxy, check forwarded headers
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
    ];

    foreach ($ipKeys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];

            // Handle comma-separated list of IPs (take first, client IP)
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
            }

            // Validate IP address
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $remoteAddr;
}

/**
 * Check if IP is banned
 */
function isIpBanned(string $ip): ?string
{
    $db = Database::getInstance();
    if (!$db) {
        return null;
    }

    $stmt = $db->prepare("
        SELECT * FROM ip_bans
        WHERE ip_address = :ip
        LIMIT 1
    ");
    $stmt->execute(['ip' => $ip]);
    $ban = $stmt->fetch();

    if (!$ban) {
        return null;
    }

    if ($ban['is_permanent']) {
        return 'permanent';
    }

    if ($ban['banned_until'] && strtotime($ban['banned_until']) > time()) {
        return 'temporary';
    }

    return null;
}

/**
 * Record a failed login attempt
 */
function recordFailedAttempt(string $ip, string $reason = 'Login failed'): void
{
    $db = Database::getInstance();
    if (!$db) {
        return;
    }

    $config = App::config('security', []);
    $banThreshold = $config['ip_ban_threshold'] ?? 5;
    $permanentThreshold = $config['ip_ban_permanent_threshold'] ?? 10;
    $banDuration = $config['ip_ban_duration'] ?? 900;

    // Get existing record
    $stmt = $db->prepare("SELECT * FROM ip_bans WHERE ip_address = :ip LIMIT 1");
    $stmt->execute(['ip' => $ip]);
    $existing = $stmt->fetch();

    $attempts = ($existing['failed_attempts'] ?? 0) + 1;

    if ($attempts >= $permanentThreshold) {
        // Permanent ban
        $banUntil = null;
        $isPermanent = true;
    } elseif ($attempts >= $banThreshold) {
        // Temporary ban
        $banUntil = date('Y-m-d H:i:s', time() + $banDuration);
        $isPermanent = false;
    } else {
        $banUntil = null;
        $isPermanent = false;
    }

    if ($existing) {
        $stmt = $db->prepare("
            UPDATE ip_bans
            SET failed_attempts = :attempts,
                last_attempt_at = NOW(),
                banned_until = :banned_until,
                is_permanent = :is_permanent,
                reason = :reason,
                updated_at = NOW()
            WHERE ip_address = :ip
        ");
    } else {
        $stmt = $db->prepare("
            INSERT INTO ip_bans (ip_address, failed_attempts, last_attempt_at, banned_until, is_permanent, reason)
            VALUES (:ip, :attempts, NOW(), :banned_until, :is_permanent, :reason)
        ");
    }

    $stmt->execute([
        'ip' => $ip,
        'attempts' => $attempts,
        'banned_until' => $banUntil,
        'is_permanent' => $isPermanent ? 1 : 0,
        'reason' => $reason,
    ]);
}

/**
 * Reset failed attempts on successful login
 */
function resetFailedAttempts(string $ip): void
{
    $db = Database::getInstance();
    if (!$db) {
        return;
    }

    $stmt = $db->prepare("DELETE FROM ip_bans WHERE ip_address = :ip AND is_permanent = 0");
    $stmt->execute(['ip' => $ip]);
}

/**
 * Sanitize a filename
 */
function sanitizeFilename(string $filename): string
{
    // Dangerous system files that should never be allowed
    $dangerousFiles = [
        'passwd', 'shadow', 'group', 'hosts', 'sudoers',
        '.htaccess', '.htpasswd', 'web.config', '.env',
        'config.php', 'database.php'
    ];

    // Remove directory traversal attempts
    $filename = basename($filename);

    // Replace HTML tags with underscore before stripping (preserves word boundaries)
    $filename = preg_replace('/<[^>]*>/', '_', $filename);

    // Strip any remaining tags
    $filename = strip_tags($filename);

    // Check if the filename (without extension) is a dangerous system file
    $nameWithoutExt = strtolower(pathinfo($filename, PATHINFO_FILENAME));
    if (in_array($nameWithoutExt, $dangerousFiles, true)) {
        Logger::security('Dangerous filename blocked', ['filename' => $filename]);
        // Return a safe default name with the original extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext ? "file.{$ext}" : 'file.txt';
    }

    // Replace unsafe characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

    // Remove multiple underscores
    $filename = preg_replace('/_+/', '_', $filename);

    // Remove leading/trailing underscores and dots
    $filename = trim($filename, '_.');

    // Limit length
    if (strlen($filename) > 200) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $filename = substr($name, 0, 200 - strlen($ext) - 1) . '.' . $ext;
    }

    return $filename ?: 'unnamed';
}

/**
 * Generate a secure filename
 */
function generateSecureFilename(string $originalName, string $prefix = ''): string
{
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $timestamp = time();
    $random = bin2hex(random_bytes(8));

    if ($prefix) {
        return "{$prefix}_{$timestamp}_{$random}.{$ext}";
    }

    return "{$timestamp}_{$random}.{$ext}";
}

/**
 * Validate file type by MIME type
 */
function validateFileType(string $filepath, array $allowedTypes): bool
{
    if (!file_exists($filepath)) {
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filepath);
    finfo_close($finfo);

    return in_array($mimeType, $allowedTypes, true);
}

/**
 * Check for suspicious content in file
 */
function isSuspiciousFile(string $filepath): bool
{
    $content = file_get_contents($filepath, false, null, 0, 1024);

    // Check for PHP code
    if (preg_match('/<\?php|<\?=|<\?(?!\s*xml)/i', $content)) {
        return true;
    }

    // Check for JavaScript in images (XSS attempts)
    if (preg_match('/<script|javascript:/i', $content)) {
        return true;
    }

    return false;
}

/**
 * Clean user input (basic XSS prevention)
 * @param mixed $input String or array to clean
 * @return string|array Cleaned input
 */
function cleanInput($input): string|array
{
    if (is_array($input)) {
        return array_map('cleanInput', $input);
    }

    // Ensure we're working with a string
    $input = (string) $input;

    // Remove null bytes
    $input = str_replace(chr(0), '', $input);

    // Strip HTML tags (keep safe ones if needed)
    $input = strip_tags($input);

    // Trim whitespace
    $input = trim($input);

    return $input;
}

/**
 * Validate and clean HTML input (for rich text)
 * Enhanced to cover more XSS attack vectors
 */
function cleanHtml(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';

    // Strip all tags except allowed
    $html = strip_tags($html, $allowed);

    // Remove all event handlers (onclick, onerror, onload, etc.)
    // Handle double-quoted attributes
    $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html);
    // Handle single-quoted attributes
    $html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/i', '', $html);
    // Handle unquoted attributes
    $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $html);

    // Remove dangerous URL schemes (javascript:, data:, vbscript:)
    $html = preg_replace('/href\s*=\s*["\']?\s*(javascript|data|vbscript):[^"\'>\s]*/i', 'href="#"', $html);

    // Remove style attributes (can be used for CSS-based attacks)
    $html = preg_replace('/\s+style\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/\s+style\s*=\s*[^\s>]+/i', '', $html);

    return $html;
}

/**
 * Create a secure hash for comparison
 */
function secureHash(string $value): string
{
    return hash('sha256', $value);
}

/**
 * Constant-time string comparison
 */
function secureCompare(string $a, string $b): bool
{
    return hash_equals($a, $b);
}

/**
 * Generate a secure random token
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Rate limiting check
 * Uses file locking to prevent race conditions under high concurrency
 */
function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds): bool
{
    $cacheDir = STORAGE_PATH . '/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . '/rate_' . md5($key) . '.json';
    $now = time();

    // Open file for reading and writing (create if not exists)
    $fp = fopen($cacheFile, 'c+');
    if (!$fp) {
        // If we can't open the file, fail open (allow the request)
        return true;
    }

    // Acquire exclusive lock to prevent race conditions
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return true;
    }

    try {
        // Read existing data
        $content = stream_get_contents($fp);
        $data = $content ? (json_decode($content, true) ?: []) : [];

        // Clean old entries
        $data = array_filter($data, fn($time) => $time > ($now - $decaySeconds));
        $data = array_values($data); // Re-index array

        // Check limit
        if (count($data) >= $maxAttempts) {
            return false;
        }

        // Add current attempt
        $data[] = $now;

        // Write updated data
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));

        return true;
    } finally {
        // Always release lock and close file
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
