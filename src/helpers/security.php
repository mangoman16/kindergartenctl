<?php
/**
 * Security Helper Functions
 */

/**
 * Get the client IP address
 */
function getClientIp(): string
{
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    foreach ($ipKeys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];

            // Handle comma-separated list of IPs
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

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
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
    // Remove directory traversal attempts
    $filename = basename($filename);

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
 */
function cleanInput($input): string
{
    if (is_array($input)) {
        return array_map('cleanInput', $input);
    }

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
 */
function cleanHtml(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6>';

    // Strip all tags except allowed
    $html = strip_tags($html, $allowed);

    // Remove dangerous attributes
    $html = preg_replace('/\s*on\w+="[^"]*"/i', '', $html);
    $html = preg_replace('/\s*on\w+=\'[^\']*\'/i', '', $html);

    // Clean javascript: urls in href
    $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);

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
 */
function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds): bool
{
    $cacheFile = STORAGE_PATH . '/cache/rate_' . md5($key) . '.json';

    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?: [];
    }

    // Clean old entries
    $now = time();
    $data = array_filter($data, fn($time) => $time > ($now - $decaySeconds));

    // Check limit
    if (count($data) >= $maxAttempts) {
        return false;
    }

    // Add current attempt
    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));

    return true;
}
