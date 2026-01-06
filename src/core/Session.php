<?php
/**
 * Session Management Class
 */

class Session
{
    private static bool $started = false;

    /**
     * Start the session
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = App::config('session', []);

        // Session configuration
        ini_set('session.name', $config['name'] ?? 'kindergarten_session');
        ini_set('session.cookie_lifetime', '0'); // Session cookie
        ini_set('session.gc_maxlifetime', (string)($config['lifetime'] ?? 86400));
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', $config['samesite'] ?? 'Lax');
        ini_set('session.use_strict_mode', '1');

        if ($config['secure'] ?? false) {
            ini_set('session.cookie_secure', '1');
        }

        session_start();
        self::$started = true;

        // Regenerate session ID periodically for security
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            // Regenerate every 30 minutes
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }

        // Check session expiry
        $lifetime = $config['lifetime'] ?? 86400;
        if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity'] > $lifetime)) {
            self::destroy();
            self::start();
        }
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Get a session value
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get and remove a session value (flash data)
     */
    public static function flash(string $key, $default = null)
    {
        $value = self::get($key, $default);
        self::remove($key);
        return $value;
    }

    /**
     * Set a flash message
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Get flash messages
     */
    public static function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }

    /**
     * Destroy the session
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Get CSRF token (generate if not exists)
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token']) || !isset($_SESSION['_csrf_token_time'])) {
            self::regenerateCsrf();
        }

        // Regenerate token if expired
        $lifetime = App::config('security.csrf_token_lifetime', 3600);
        if (time() - $_SESSION['_csrf_token_time'] > $lifetime) {
            self::regenerateCsrf();
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Regenerate CSRF token
     */
    public static function regenerateCsrf(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token_time'] = time();
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(string $token): bool
    {
        if (!isset($_SESSION['_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Set old input values for form repopulation
     */
    public static function setOldInput(array $input): void
    {
        $_SESSION['_old_input'] = $input;
    }

    /**
     * Get old input value
     */
    public static function oldInput(string $key, $default = null)
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    /**
     * Clear old input
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }

    /**
     * Set validation errors
     */
    public static function setErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }

    /**
     * Get validation errors
     */
    public static function getErrors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        return $errors;
    }

    /**
     * Check if there are any errors
     */
    public static function hasErrors(): bool
    {
        return !empty($_SESSION['_errors']);
    }
}
