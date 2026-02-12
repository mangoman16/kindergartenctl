<?php
/**
 * =====================================================================================
 * BOX MODEL - Physical Storage Containers
 * =====================================================================================
 *
 * Represents physical storage boxes/containers where games and materials are kept.
 * Each box has a name, number, label, location, and can contain multiple materials.
 *
 * @package KindergartenOrganizer\Models
 * =====================================================================================
 *
 * Box Model
 */

class Box extends Model
{
    protected static string $table = 'boxes';
    protected static array $fillable = [
        'name',
        'number',
        'label',
        'location',
        'location_id',
        'description',
        'notes',
        'image_path',
    ];

    /**
     * Allowed columns for ordering
     */
    private static array $allowedOrderColumns = ['name', 'number', 'label', 'location', 'created_at', 'updated_at'];

    /**
     * Get all boxes with material count
     */
    public static function allWithMaterialCount(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Validate orderBy column to prevent SQL injection
        if (!in_array($orderBy, self::$allowedOrderColumns, true)) {
            $orderBy = 'name';
        }

        $sql = "SELECT b.*, COUNT(m.id) as material_count, l.name as location_name
                FROM boxes b
                LEFT JOIN materials m ON m.box_id = b.id
                LEFT JOIN locations l ON l.id = b.location_id
                GROUP BY b.id
                ORDER BY b.{$orderBy} {$direction}";

        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get box with material count
     */
    public static function findWithMaterialCount(int $id): ?array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT b.*, COUNT(m.id) as material_count, l.name as location_name
            FROM boxes b
            LEFT JOIN materials m ON m.box_id = b.id
            LEFT JOIN locations l ON l.id = b.location_id
            WHERE b.id = :id
            GROUP BY b.id
        ");
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get materials in this box
     */
    public static function getMaterials(int $boxId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM materials
            WHERE box_id = :box_id
            ORDER BY name ASC
        ");
        $stmt->execute(['box_id' => $boxId]);

        return $stmt->fetchAll();
    }

    /**
     * Get games in this box (via games.box_id FK)
     */
    public static function getGames(int $boxId): array
    {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT * FROM games
            WHERE box_id = :box_id
            ORDER BY name ASC
        ");
        $stmt->execute(['box_id' => $boxId]);

        return $stmt->fetchAll();
    }

    /**
     * Search boxes
     */
    public static function searchBoxes(string $query, int $limit = 50): array
    {
        return self::search($query, ['name', 'location', 'description', 'notes'], $limit);
    }

    /**
     * Get boxes for select dropdown
     */
    public static function getForSelect(): array
    {
        $db = self::getDb();

        $stmt = $db->query("SELECT id, name, number FROM boxes ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Check if box name exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        return self::valueExists('name', $name, $excludeId);
    }
}
