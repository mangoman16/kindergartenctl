<?php
/**
 * =====================================================================================
 * USER MODEL - User Accounts and Authentication
 * =====================================================================================
 *
 * Manages user accounts. password_hash and remember_token excluded from $fillable
 * for mass-assignment protection. Use dedicated methods: createUser(),
 * updatePassword(), setRememberToken(), clearRememberToken().
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * User Model
 */

class User extends Model
{
    protected static string $table = 'users';
    /**
     * AI NOTE: password_hash and remember_token are intentionally EXCLUDED from
     * $fillable to prevent mass-assignment attacks. These sensitive fields must
     * only be set via their dedicated methods: createUser(), updatePassword(),
     * setRememberToken(), clearRememberToken(). Those methods use direct SQL
     * updates that bypass the $fillable filter.
     */
    protected static array $fillable = [
        'username',
        'email',
        'last_login_at',
    ];

    /**
     * Find a user by login (username or email).
     *
     * AI NOTE: Uses distinct params (:login1, :login2) because PDO native
     * prepared statements (EMULATE_PREPARES=false) cannot reuse named params.
     */
    public static function findByLogin(string $login): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE username = :login1 OR email = :login2
            LIMIT 1
        ");
        $stmt->execute(['login1' => $login, 'login2' => $login]);

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
     * Create a new user with password hash.
     *
     * AI NOTE: Uses direct SQL INSERT instead of Model::create() because
     * password_hash is excluded from $fillable for mass-assignment protection.
     * Model::create() would filter it out, creating a user without a password.
     */
    public static function createUser(string $username, string $email, string $password): ?int
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash)
            VALUES (:username, :email, :hash)
        ");

        try {
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            return null;
        }
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
