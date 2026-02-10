<?php
/**
 * =====================================================================================
 * TAG MODEL - Themes and Categories for Games
 * =====================================================================================
 *
 * Tags represent themes (seasons, holidays, colors) assigned to games via game_tags.
 * quickCreate() trims name before duplicate check to prevent mismatch.
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * Tag Model (Themes)
 */

class Tag extends Model
{
    protected static string $table = 'tags';
    protected static array $fillable = [
        'name',
        'description',
        'image_path',
        'color',
    ];

    /**
     * Allowed columns for ordering
     */
    private static array $allowedOrderColumns = ['name', 'color', 'created_at', 'updated_at'];

    /**
     * Get all tags with game count
     */
    public static function allWithGameCount(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Validate orderBy column to prevent SQL injection
        if (!in_array($orderBy, self::$allowedOrderColumns, true)) {
            $orderBy = 'name';
        }

        $sql = "SELECT t.*, COUNT(gt.game_id) as game_count
                FROM tags t
                LEFT JOIN game_tags gt ON gt.tag_id = t.id
                GROUP BY t.id
                ORDER BY t.{$orderBy} {$direction}";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get tag with game count
     */
    public static function findWithGameCount(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT t.*, COUNT(gt.game_id) as game_count
            FROM tags t
            LEFT JOIN game_tags gt ON gt.tag_id = t.id
            WHERE t.id = :id
            GROUP BY t.id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get games with this tag
     */
    public static function getGames(int $tagId, int $limit = 0): array
    {
        $db = self::getDb();

        $sql = "SELECT g.* FROM games g
                INNER JOIN game_tags gt ON gt.game_id = g.id
                WHERE gt.tag_id = :tag_id
                ORDER BY g.name ASC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindValue('tag_id', $tagId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare($sql);
            $stmt->execute(['tag_id' => $tagId]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get tags for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name, color FROM tags ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Check if tag name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }

    /**
     * Quick create a tag (for inline creation in game form).
     *
     * AI NOTE: Trims the name BEFORE the existence check to prevent a mismatch
     * where "  Tag  " passes the duplicate check but stores as "Tag", potentially
     * creating a duplicate if "Tag" already exists.
     *
     * @param string $name Raw tag name from user input
     * @return int|null New tag ID, or null if name already exists or insert failed
     */
    public static function quickCreate(string $name): ?int
    {
        $name = trim($name);

        if (empty($name) || self::nameExists($name)) {
            return null;
        }

        return self::create([
            'name' => $name,
        ]);
    }

    /**
     * Search tags by name
     */
    public static function searchByName(string $query, int $limit = 10): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT id, name, color FROM tags
            WHERE name LIKE :query
            ORDER BY name ASC
            LIMIT :limit
        ");
        $stmt->bindValue('query', '%' . $query . '%', PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
