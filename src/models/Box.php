<?php
/**
 * Box Model
 */

class Box extends Model
{
    protected static string $table = 'boxes';
    protected static array $fillable = [
        'name',
        'number',
        'location',
        'description',
        'notes',
        'image_path',
    ];

    /**
     * Get all boxes with material count
     */
    public static function allWithMaterialCount(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $db = self::getDb();
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT b.*, COUNT(m.id) as material_count
                FROM boxes b
                LEFT JOIN materials m ON m.box_id = b.id
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
            SELECT b.*, COUNT(m.id) as material_count
            FROM boxes b
            LEFT JOIN materials m ON m.box_id = b.id
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
