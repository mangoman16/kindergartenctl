<?php
/**
 * =====================================================================================
 * PASSWORD RESET MODEL - Token-Based Password Recovery
 * =====================================================================================
 *
 * Flow: user requests reset -> plain token emailed -> hashed token stored in DB ->
 * user submits token -> hash compared -> password updated.
 * cleanupExpired() removes both expired AND already-used tokens.
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * Password Reset Model
 */

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';
    protected static array $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    /**
     * Find a valid (not used, not expired) reset by token hash
     */
    public static function findValidByHash(string $tokenHash): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM password_resets
            WHERE token_hash = :hash
              AND expires_at > NOW()
              AND used_at IS NULL
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['hash' => $tokenHash]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Mark a token as used
     */
    public static function markUsed(string $tokenHash): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE token_hash = :hash
        ");

        return $stmt->execute(['hash' => $tokenHash]);
    }

    /**
     * Delete expired and already-used tokens (cleanup).
     *
     * AI NOTE: Removes tokens that have either passed their expiry date OR
     * have already been used (used_at IS NOT NULL). Both types are safe to
     * delete as they can no longer be redeemed.
     */
    public static function cleanupExpired(): int
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            DELETE FROM password_resets
            WHERE expires_at < NOW() OR used_at IS NOT NULL
        ");
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Delete all tokens for a user
     */
    public static function deleteForUser(int $userId): bool
    {
        $db = self::getDb();

        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }
}
