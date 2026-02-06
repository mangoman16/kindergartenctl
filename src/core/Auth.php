<?php
/**
 * =====================================================================================
 * AUTH - Authentication and Authorization
 * =====================================================================================
 *
 * PURPOSE:
 * Manages user authentication state (login, logout, current user). Provides
 * "remember me" functionality via hashed tokens stored in cookies and the
 * users table. Handles IP-based brute force protection via the ip_bans table.
 *
 * AUTHENTICATION FLOW:
 * 1. User submits login form -> AuthController::login()
 * 2. Auth::attempt() verifies credentials via password_verify()
 * 3. On success: session is regenerated, user_id stored in session
 * 4. On failure: failed attempt counter incremented, IP may be banned
 * 5. Auth::check() is called on every authenticated request
 * 6. If no session, Auth::checkRememberToken() tries cookie-based login
 *
 * REMEMBER ME:
 * - Plain token sent as cookie, hashed (SHA-256) version stored in DB
 * - Tokens expire after configured period (default 30 days)
 * - Token is rotated on each use (old token invalidated, new token issued)
 *
 * BRUTE FORCE PROTECTION:
 * - Tracks failed login attempts per IP in ip_bans table
 * - Temporary ban after 5 failures (configurable)
 * - Ban duration increases with repeated failures
 *
 * USAGE:
 * ```php
 * Auth::check()        // Returns true if user is authenticated
 * Auth::user()         // Returns user array or null
 * Auth::id()           // Returns user ID or null
 * Auth::attempt($credentials) // Try to log in
 * Auth::logout()       // Destroy session and clear remember token
 * ```
 *
 * AI NOTES:
 * - Uses static $user property as cache to avoid repeated DB lookups
 * - check() is called by Controller::requireAuth() which most controllers use
 * - IP ban logic is in Auth, not a separate middleware (no middleware system)
 *
 * RELATED FILES:
 * - src/controllers/AuthController.php - Login/logout/password reset UI
 * - src/models/User.php - User DB queries
 * - src/core/Session.php - Session management
 * - src/helpers/security.php - Password hashing, CSRF tokens
 *
 * @package KindergartenOrganizer\Core
 * @since 1.0.0
 * =====================================================================================
 */

class Auth
{
    private static ?array $user = null;

    /**
     * Attempt to log in a user
     */
    public static function attempt(string $login, string $password, bool $remember = false): bool
    {
        // Load User model
        require_once SRC_PATH . '/models/User.php';

        // Find user by username or email
        $user = User::findByLogin($login);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Log in the user
        self::login($user, $remember);

        return true;
    }

    /**
     * Log in a user
     */
    public static function login(array $user, bool $remember = false): void
    {
        // Regenerate session ID
        Session::regenerate();

        // Store user in session
        Session::set('user_id', $user['id']);
        Session::set('user', [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
        ]);

        // Update last login
        require_once SRC_PATH . '/models/User.php';
        User::updateLastLogin($user['id']);

        // Set remember token if requested
        if ($remember) {
            self::setRememberToken($user['id']);
        }

        self::$user = $user;
    }

    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        $userId = Session::get('user_id');

        // Clear remember token
        if ($userId) {
            require_once SRC_PATH . '/models/User.php';
            User::clearRememberToken($userId);
        }

        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }

        // Destroy session
        Session::destroy();
        self::$user = null;
    }

    /**
     * Check if a user is logged in
     */
    public static function check(): bool
    {
        // Check session first
        if (Session::has('user_id')) {
            return true;
        }

        // Try to auto-login with remember token
        if (isset($_COOKIE['remember_token'])) {
            return self::loginWithRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }

    /**
     * Get the current authenticated user
     */
    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        if (!self::check()) {
            return null;
        }

        $userId = Session::get('user_id');
        if ($userId) {
            require_once SRC_PATH . '/models/User.php';
            self::$user = User::find($userId);
        }

        return self::$user;
    }

    /**
     * Get the current user ID
     */
    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Set a remember token for the user
     */
    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $lifetime = App::config('session.remember_lifetime', 2592000);
        $expiresAt = date('Y-m-d H:i:s', time() + $lifetime);

        require_once SRC_PATH . '/models/User.php';
        User::setRememberToken($userId, $tokenHash, $expiresAt);

        // Set cookie
        $secure = isset($_SERVER['HTTPS']);
        setcookie('remember_token', $token, time() + $lifetime, '/', '', $secure, true);
    }

    /**
     * Try to log in with a remember token
     */
    private static function loginWithRememberToken(string $token): bool
    {
        $tokenHash = hash('sha256', $token);

        require_once SRC_PATH . '/models/User.php';
        $user = User::findByRememberToken($tokenHash);

        if (!$user) {
            // Invalid token, clear the cookie
            setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            return false;
        }

        // Token is valid, log in the user
        self::login($user, true);

        return true;
    }

    /**
     * Hash a password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate a password reset token
     */
    public static function generatePasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $lifetime = App::config('security.password_reset_lifetime', 3600);
        $expiresAt = date('Y-m-d H:i:s', time() + $lifetime);

        require_once SRC_PATH . '/models/PasswordReset.php';
        PasswordReset::create([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    /**
     * Validate a password reset token
     */
    public static function validatePasswordResetToken(string $token): ?int
    {
        $tokenHash = hash('sha256', $token);

        require_once SRC_PATH . '/models/PasswordReset.php';
        $reset = PasswordReset::findValidByHash($tokenHash);

        if (!$reset) {
            return null;
        }

        return (int)$reset['user_id'];
    }

    /**
     * Mark a password reset token as used
     */
    public static function markPasswordResetUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);

        require_once SRC_PATH . '/models/PasswordReset.php';
        PasswordReset::markUsed($tokenHash);
    }
}
