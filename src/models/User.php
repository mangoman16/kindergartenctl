<?php
/**
 * User Model
 */

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = [
        'username',
        'email',
        'password_hash',
        'remember_token',
        'remember_token_expires_at',
        'last_login_at',
    ];

    /**
     * Find a user by login (username or email)
     */
    public static function findByLogin(string $login): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE username = :login OR email = :login
            LIMIT 1
        ");
        $stmt->execute(['login' => $login]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find a user by email
     */
    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', $email);
    }

    /**
     * Find a user by remember token
     */
    public static function findByRememberToken(string $tokenHash): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE remember_token = :token
              AND remember_token_expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute(['token' => $tokenHash]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new user
     */
    public static function createUser(string $username, string $email, string $password): ?int
    {
        return self::create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Update user's last login time
     */
    public static function updateLastLogin(int $userId): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE users SET last_login_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Set remember token
     */
    public static function setRememberToken(int $userId, string $tokenHash, string $expiresAt): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE users
            SET remember_token = :token,
                remember_token_expires_at = :expires
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $userId,
            'token' => $tokenHash,
            'expires' => $expiresAt,
        ]);
    }

    /**
     * Clear remember token
     */
    public static function clearRememberToken(int $userId): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE users
            SET remember_token = NULL,
                remember_token_expires_at = NULL
            WHERE id = :id
        ");

        return $stmt->execute(['id' => $userId]);
    }

    /**
     * Update password
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE users
            SET password_hash = :hash
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $userId,
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Update email
     */
    public static function updateEmail(int $userId, string $newEmail): bool
    {
        return self::update($userId, ['email' => $newEmail]);
    }

    /**
     * Check if username exists
     */
    public static function usernameExists(string $username, ?int $excludeId = null): bool
    {
        return self::valueExists('username', $username, $excludeId);
    }

    /**
     * Check if email exists
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        return self::valueExists('email', $email, $excludeId);
    }
}
