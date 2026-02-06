<?php
/**
 * =====================================================================================
 * CATEGORY MODEL - Age Groups for Games
 * =====================================================================================
 *
 * Represents age group categories (2-3 years, 3-4 years, etc.).
 * Games link to categories via category_id FK (primary) and game_categories
 * junction table (additional categories).
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * Category Model (Age Groups)
 */

class Category extends Model
{
    protected static string $table = 'categories';
    protected static array $fillable = [
        'name',
        'description',
        'image_path',
        'sort_order',
    ];

    /**
     * Allowed columns for ordering
     */
    private static array $allowedOrderColumns = ['name', 'sort_order', 'created_at', 'updated_at'];

    /**
     * Get all categories with game count
     */
    public static function allWithGameCount(string $orderBy = 'sort_order', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Validate orderBy column to prevent SQL injection
        if (!in_array($orderBy, self::$allowedOrderColumns, true)) {
            $orderBy = 'sort_order';
        }

        $sql = "SELECT c.*, COUNT(gc.game_id) as game_count
                FROM categories c
                LEFT JOIN game_categories gc ON gc.category_id = c.id
                GROUP BY c.id
                ORDER BY c.{$orderBy} {$direction}";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get category with game count
     */
    public static function findWithGameCount(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT c.*, COUNT(gc.game_id) as game_count
            FROM categories c
            LEFT JOIN game_categories gc ON gc.category_id = c.id
            WHERE c.id = :id
            GROUP BY c.id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get games in this category
     */
    public static function getGames(int $categoryId, int $limit = 0): array
    {
        $db = self::getDb();

        $sql = "SELECT g.* FROM games g
                INNER JOIN game_categories gc ON gc.game_id = g.id
                WHERE gc.category_id = :category_id
                ORDER BY g.name ASC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindValue('category_id', $categoryId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare($sql);
            $stmt->execute(['category_id' => $categoryId]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get categories for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name FROM categories ORDER BY sort_order ASC, name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Check if category name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Get next sort order
     */
    public static function getNextSortOrder(): int
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT MAX(sort_order) FROM categories");
        $max = (int)$stmt->fetchColumn();

        return $max + 1;
    }
}
